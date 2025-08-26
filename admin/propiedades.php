<?php
require_once '../config/database.php';
require_once '../includes/session.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

$es_admin = isset($_SESSION['privilegio']) && $_SESSION['privilegio'] === 'administrador';
$user_id  = $_SESSION['user_id'] ?? 0;

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
function csrf_token() { return $_SESSION['csrf']; }
function csrf_check($t) { return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t); }

function subir_imagen($campo_input, $carpeta_rel = '../uploads')
{
    if (empty($_FILES[$campo_input]['name']) || $_FILES[$campo_input]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; 
    }

    if (!is_dir($carpeta_rel)) {
        @mkdir($carpeta_rel, 0775, true);
    }

    if (!isset($_FILES[$campo_input]) || $_FILES[$campo_input]['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Error al subir el archivo.');
    }

    if ($_FILES[$campo_input]['size'] > 5 * 1024 * 1024) {
        throw new RuntimeException('La imagen excede 5MB.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $_FILES[$campo_input]['tmp_name']);
    finfo_close($finfo);
    $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    if (!isset($permitidos[$mime])) {
        throw new RuntimeException('Tipo de imagen no permitido.');
    }

    $ext   = $permitidos[$mime];
    $fname = 'propiedad_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

    $dest_abs = rtrim($carpeta_rel, '/').'/'.$fname;

    if (!move_uploaded_file($_FILES[$campo_input]['tmp_name'], $dest_abs)) {
        throw new RuntimeException('No se pudo mover el archivo subido.');
    }

    return 'uploads/' . $fname;
}

$mensaje = '';
$errores = [];

if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id'])) {
    try {
        $id = (int)$_GET['id'];
        $where_clause = $es_admin ? '' : ' AND agente_id = :agente_id';
        $query = "DELETE FROM propiedades WHERE id = :id" . $where_clause;
        $stmt  = $db->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        if (!$es_admin) { $stmt->bindValue(':agente_id', $user_id, PDO::PARAM_INT); }
        if ($stmt->execute() && $stmt->rowCount() > 0) {
            $mensaje = '<div class="alert alert-success">Propiedad eliminada correctamente</div>';
        } else {
            $mensaje = '<div class="alert alert-warning">No se pudo eliminar (verifique permisos)</div>';
        }
    } catch (Throwable $e) {
        $mensaje = '<div class="alert alert-danger">Error: '.$e->getMessage().'</div>';
    }
}

/* -------------------------
   Cargar para edición
------------------------- */
$propiedad_editar = null;
if (isset($_GET['editar'])) {
    $id = (int)$_GET['editar'];
    $where_clause = $es_admin ? '' : ' AND p.agente_id = :agente_id';
    $query = "SELECT * FROM propiedades p WHERE p.id = :id".$where_clause." LIMIT 1";
    $stmt  = $db->prepare($query);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    if (!$es_admin) { $stmt->bindValue(':agente_id', $user_id, PDO::PARAM_INT); }
    $stmt->execute();
    $propiedad_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!csrf_check($_POST['csrf'] ?? '')) {
            throw new RuntimeException('CSRF inválido.');
        }

        $tipo      = $_POST['tipo'] ?? '';
        $destacada = isset($_POST['destacada']) ? 1 : 0;
        $titulo    = trim($_POST['titulo'] ?? '');
        $desc_b    = trim($_POST['descripcion_breve'] ?? '');
        $precio    = $_POST['precio'] ?? '';
        $ubicacion = trim($_POST['ubicacion'] ?? '');
        $desc_l    = trim($_POST['descripcion_larga'] ?? '');
        $mapa      = trim($_POST['mapa'] ?? '');

        if (!in_array($tipo, ['alquiler', 'venta'], true)) { $errores[] = 'Tipo inválido.'; }
        if ($titulo === '') { $errores[] = 'El título es obligatorio.'; }
        if ($precio === '' || !is_numeric($precio)) { $errores[] = 'Precio inválido.'; }

        if ($es_admin) {
            $agente_id = (int)($_POST['agente_id'] ?? 0);
            if ($agente_id <= 0) { $errores[] = 'Seleccione un agente de ventas.'; }
        } else {
            $agente_id = $user_id;
        }

        $imagen_destacada = null;
        if (!empty($_FILES['imagen_destacada']['name'])) {
            $imagen_destacada = subir_imagen('imagen_destacada', '../uploads');
        } else {
            if (!empty($_POST['imagen_actual'])) {
                $imagen_destacada = $_POST['imagen_actual'];
            }
        }

        if (!$errores) {
            if (!empty($_POST['id'])) {
                $id = (int)$_POST['id'];
                $where_clause = $es_admin ? '' : ' AND agente_id = :agente_id';

                $query = "UPDATE propiedades SET 
                            tipo = :tipo,
                            destacada = :destacada,
                            titulo = :titulo,
                            descripcion_breve = :desc_b,
                            precio = :precio,
                            agente_id = :agente_id,
                            descripcion_larga = :desc_l,
                            mapa = :mapa,
                            ubicacion = :ubicacion".
                          (!empty($imagen_destacada) ? ", imagen_destacada = :imagen_destacada" : "").
                          " WHERE id = :id".$where_clause;

                $stmt = $db->prepare($query);
                $stmt->bindValue(':tipo', $tipo);
                $stmt->bindValue(':destacada', $destacada, PDO::PARAM_INT);
                $stmt->bindValue(':titulo', $titulo);
                $stmt->bindValue(':desc_b', $desc_b);
                $stmt->bindValue(':precio', $precio);
                $stmt->bindValue(':agente_id', $agente_id, PDO::PARAM_INT);
                $stmt->bindValue(':desc_l', $desc_l);
                $stmt->bindValue(':mapa', $mapa);
                $stmt->bindValue(':ubicacion', $ubicacion);
                if (!empty($imagen_destacada)) { $stmt->bindValue(':imagen_destacada', $imagen_destacada); }
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                if (!$es_admin) { $stmt->bindValue(':agente_id', $user_id, PDO::PARAM_INT); }

                if ($stmt->execute() && $stmt->rowCount() >= 0) {
                    $mensaje = '<div class="alert alert-success">Propiedad actualizada</div>';
                    $propiedad_editar = null; // limpiar form
                } else {
                    $mensaje = '<div class="alert alert-warning">No se realizaron cambios</div>';
                }
            } else {
                $query = "INSERT INTO propiedades
                          (tipo, destacada, titulo, descripcion_breve, precio, agente_id, imagen_destacada, descripcion_larga, mapa, ubicacion, fecha_creacion)
                          VALUES
                          (:tipo, :destacada, :titulo, :desc_b, :precio, :agente_id, :imagen_destacada, :desc_l, :mapa, :ubicacion, NOW())";
                $stmt = $db->prepare($query);
                $stmt->bindValue(':tipo', $tipo);
                $stmt->bindValue(':destacada', $destacada, PDO::PARAM_INT);
                $stmt->bindValue(':titulo', $titulo);
                $stmt->bindValue(':desc_b', $desc_b);
                $stmt->bindValue(':precio', $precio);
                $stmt->bindValue(':agente_id', $agente_id, PDO::PARAM_INT);
                $stmt->bindValue(':imagen_destacada', $imagen_destacada);
                $stmt->bindValue(':desc_l', $desc_l);
                $stmt->bindValue(':mapa', $mapa);
                $stmt->bindValue(':ubicacion', $ubicacion);

                if ($stmt->execute()) {
                    $mensaje = '<div class="alert alert-success">Propiedad creada</div>';
                } else {
                    $mensaje = '<div class="alert alert-danger">No se pudo crear</div>';
                }
            }
        } else {
            $mensaje = '<div class="alert alert-danger">'.implode('<br>', array_map('htmlspecialchars', $errores)).'</div>';
        
            $propiedad_editar = [
                'id' => $_POST['id'] ?? null,
                'tipo' => $tipo,
                'destacada' => $destacada,
                'titulo' => $titulo,
                'descripcion_breve' => $desc_b,
                'precio' => $precio,
                'agente_id' => $agente_id,
                'imagen_destacada' => $_POST['imagen_actual'] ?? null,
                'descripcion_larga' => $desc_l,
                'mapa' => $mapa,
                'ubicacion' => $ubicacion,
            ];
        }
    } catch (Throwable $e) {
        $mensaje = '<div class="alert alert-danger">Error: '.$e->getMessage().'</div>';
    }
}

$where_clause = $es_admin ? '' : ' WHERE p.agente_id = :agente_id';
$query_list = "SELECT p.*, u.nombre AS agente_nombre 
               FROM propiedades p 
               LEFT JOIN usuarios u ON p.agente_id = u.id
               $where_clause
               ORDER BY p.fecha_creacion DESC";
$stmt_list = $db->prepare($query_list);
if (!$es_admin) { $stmt_list->bindValue(':agente_id', $user_id, PDO::PARAM_INT); }
$stmt_list->execute();
$lista = $stmt_list->fetchAll(PDO::FETCH_ASSOC);

$agentes = [];
if ($es_admin) {
    $stmt_ag = $db->query("SELECT id, nombre FROM usuarios ORDER BY nombre ASC");
    $agentes = $stmt_ag->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $es_admin ? 'Administrar Propiedades' : 'Mis Propiedades'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;background:#f5f6f8;margin:0}
        .container{max-width:1100px;margin:30px auto;padding:0 16px}
        .card{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.06);padding:20px;margin-bottom:20px}
        h1{margin:0 0 10px}
        .grid{display:grid;grid-template-columns:repeat(12,1fr);gap:16px}
        .form-group{display:flex;flex-direction:column;gap:6px}
        label{font-weight:600;font-size:.95rem}
        input[type="text"],input[type="number"],textarea,select{border:1px solid #dfe3e7;border-radius:8px;padding:10px;font-size:1rem;background:#fff}
        textarea{min-height:90px}
        .row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .btn{display:inline-block;background:#1a73e8;color:#fff;border:none;border-radius:8px;padding:10px 16px;text-decoration:none;cursor:pointer}
        .btn.secondary{background:#6b7280}
        .btn.danger{background:#dc2626}
        .btn.warning{background:#f59e0b}
        .table{width:100%;border-collapse:collapse}
        .table th,.table td{padding:10px;border-bottom:1px solid #eee;text-align:left}
        .alert{padding:10px 12px;border-radius:8px;margin:10px 0}
        .alert-success{background:#e8f8ee;color:#146c43}
        .alert-danger{background:#fee2e2;color:#991b1b}
        .alert-warning{background:#fffbeb;color:#92400e}
        .switch{display:flex;align-items:center;gap:8px}
        .img-thumb{width:90px;height:64px;object-fit:cover;border-radius:8px;border:1px solid #eee}
        @media (max-width: 800px){.row{grid-template-columns:1fr}}
    </style>
</head>
<body>
<div class="container">

   <div class="admin-header">
        <h1>Gestionar Propiedades</h1>
        <div class="admin-nav">
            <a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a>
            <a href="personalizar.php"><i class="fas fa-palette"></i> Personalizar Página</a>
            <a href="usuarios.php"><i class="fas fa-users"></i> Gestionar Usuarios</a>
            <a href="propiedades.php"><i class="fas fa-building"></i> Propiedades</a>
            <a href="galeria.php"><i class="fas fa-images"></i> Galería de Imágenes</a>
            <a href="perfil.php"><i class="fas fa-building"></i> Mi perfil</a>
            <a href="../includes/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </div>
    </div>

    <div class="card">
        <h2 style="margin-top:0"><i class="fas fa-plus-circle"></i> <?php echo $propiedad_editar ? 'Editar Propiedad' : 'Nueva Propiedad'; ?></h2>
        <form method="POST" enctype="multipart/form-data" class="grid" style="grid-template-columns:repeat(12,1fr);gap:16px;">
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
            <?php if ($propiedad_editar): ?>
                <input type="hidden" name="id" value="<?php echo (int)$propiedad_editar['id']; ?>">
                <input type="hidden" name="imagen_actual" value="<?php echo htmlspecialchars($propiedad_editar['imagen_destacada'] ?? ''); ?>">
            <?php endif; ?>

            <div class="form-group" style="grid-column: span 4;">
                <label for="tipo">Tipo</label>
                <select name="tipo" id="tipo" required>
                    <?php
                        $tipo_val = $propiedad_editar['tipo'] ?? 'venta';
                        $opts = ['venta'=>'Venta','alquiler'=>'Alquiler'];
                        foreach ($opts as $k=>$v) {
                            $sel = ($tipo_val===$k) ? 'selected' : '';
                            echo "<option value=\"$k\" $sel>$v</option>";
                        }
                    ?>
                </select>
            </div>

            <div class="form-group switch" style="grid-column: span 2;">
                <input type="checkbox" id="destacada" name="destacada" <?php echo !empty($propiedad_editar['destacada']) ? 'checked' : ''; ?>>
                <label for="destacada">Destacada</label>
            </div>

            <div class="form-group" style="grid-column: span 6;">
                <label for="precio">Precio</label>
                <input type="number" step="0.01" min="0" id="precio" name="precio" required
                       value="<?php echo htmlspecialchars($propiedad_editar['precio'] ?? ''); ?>">
            </div>

            <div class="form-group" style="grid-column: span 12;">
                <label for="titulo">Título</label>
                <input type="text" id="titulo" name="titulo" required
                       value="<?php echo htmlspecialchars($propiedad_editar['titulo'] ?? ''); ?>">
            </div>

            <div class="form-group" style="grid-column: span 12;">
                <label for="descripcion_breve">Descripción breve</label>
                <textarea id="descripcion_breve" name="descripcion_breve"><?php
                    echo htmlspecialchars($propiedad_editar['descripcion_breve'] ?? '');
                ?></textarea>
            </div>

            <div class="form-group" style="grid-column: span 12;">
                <label for="descripcion_larga">Descripción larga</label>
                <textarea id="descripcion_larga" name="descripcion_larga"><?php
                    echo htmlspecialchars($propiedad_editar['descripcion_larga'] ?? '');
                ?></textarea>
            </div>

            <div class="form-group" style="grid-column: span 6;">
                <label for="ubicacion">Ubicación</label>
                <input type="text" id="ubicacion" name="ubicacion"
                       value="<?php echo htmlspecialchars($propiedad_editar['ubicacion'] ?? ''); ?>">
            </div>

            <div class="form-group" style="grid-column: span 6;">
                <label for="mapa">Mapa (dirección para Google Maps)</label>
                <input type="text" id="mapa" name="mapa"
                       placeholder="Ej: Cañas, Guanacaste, Costa Rica"
                       value="<?php echo htmlspecialchars($propiedad_editar['mapa'] ?? ''); ?>">
                <small>En el detalle se mostrará botón "Ver en Google Maps".</small>
            </div>

            <?php if ($es_admin): ?>
            <div class="form-group" style="grid-column: span 6;">
                <label for="agente_id">Agente de ventas</label>
                <select id="agente_id" name="agente_id" required>
                    <option value="">-- Seleccione --</option>
                    <?php
                    $sel_ag = $propiedad_editar['agente_id'] ?? '';
                    foreach ($agentes as $ag) {
                        $s = ($sel_ag == $ag['id']) ? 'selected' : '';
                        echo '<option value="'.(int)$ag['id'].'" '.$s.'>'.htmlspecialchars($ag['nombre']).'</option>';
                    }
                    ?>
                </select>
            </div>
            <?php else: ?>
                <input type="hidden" name="agente_id" value="<?php echo (int)$user_id; ?>">
            <?php endif; ?>

            <div class="form-group" style="grid-column: span 6;">
                <label for="imagen_destacada">Imagen destacada (JPG/PNG/GIF/WEBP, máx 5MB)</label>
                <input type="file" id="imagen_destacada" name="imagen_destacada" accept="image/*">
                <?php if (!empty($propiedad_editar['imagen_destacada'])): ?>
                    <div style="margin-top:8px">
                        <img src="../<?php echo htmlspecialchars($propiedad_editar['imagen_destacada']); ?>" class="img-thumb" alt="Actual">
                    </div>
                <?php endif; ?>
            </div>

            <div style="grid-column: span 12;display:flex;gap:8px;align-items:center">
                <button class="btn" type="submit">
                    <i class="fas fa-save"></i> <?php echo $propiedad_editar ? 'Guardar cambios' : 'Crear propiedad'; ?>
                </button>
                <?php if ($propiedad_editar): ?>
                    <a class="btn secondary" href="propiedades.php"><i class="fas fa-ban"></i> Cancelar edición</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <h2 style="margin-top:0"><i class="fas fa-list"></i> <?php echo $es_admin ? 'Todas las Propiedades' : 'Mis Propiedades'; ?></h2>
        <table class="table">
            <thead>
            <tr>
                <th>Imagen</th>
                <th>Título</th>
                <th>Tipo</th>
                <th>Precio</th>
                <th>Destacada</th>
                <?php if ($es_admin): ?><th>Agente</th><?php endif; ?>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($lista as $p): ?>
                <tr>
                    <td>
                        <?php if (!empty($p['imagen_destacada']) && file_exists('../'.$p['imagen_destacada'])): ?>
                            <img class="img-thumb" src="../<?php echo htmlspecialchars($p['imagen_destacada']); ?>" alt="<?php echo htmlspecialchars($p['titulo']); ?>">
                        <?php else: ?>
                            <span style="color:#9ca3af">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($p['titulo']); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($p['tipo'])); ?></td>
                    <td><?php echo '₡' . number_format($p['precio'], 0, ',', '.'); ?></td>
                    <td><?php echo !empty($p['destacada']) ? 'Sí' : 'No'; ?></td>
                    <?php if ($es_admin): ?>
                        <td><?php echo htmlspecialchars($p['agente_nombre'] ?? ''); ?></td>
                    <?php endif; ?>
                    <td>
                        <a class="btn warning" href="propiedades.php?editar=<?php echo (int)$p['id']; ?>"><i class="fas fa-edit"></i> Editar</a>
                        <a class="btn danger" href="propiedades.php?accion=eliminar&id=<?php echo (int)$p['id']; ?>"
                           onclick="return confirm('¿Eliminar propiedad?');"><i class="fas fa-trash"></i> Eliminar</a>
                        <a class="btn secondary" target="_blank" href="../propiedad.php?id=<?php echo (int)$p['id']; ?>">
                            <i class="fas fa-external-link-alt"></i> Ver
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($lista)): ?>
                <tr><td colspan="<?php echo $es_admin?7:6; ?>" style="color:#6b7280">No hay propiedades.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>
