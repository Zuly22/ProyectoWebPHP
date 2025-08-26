<?php
require_once '../config/database.php';
require_once '../includes/session.php';

// Verificar login y rol
if (!isset($_SESSION['user_id']) || $_SESSION['privilegio'] !== 'agente_ventas') {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$error   = '';

// ========== HANDLER FORM ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add') {
        // Campos
        $titulo             = trim($_POST['titulo'] ?? '');
        $descripcion_breve  = trim($_POST['descripcion'] ?? ''); // en el form se llama "descripcion"
        $precio             = (float)($_POST['precio'] ?? 0);
        $tipo               = $_POST['tipo'] ?? '';
        $destacada          = isset($_POST['destacada']) ? 1 : 0;
        $descripcion_larga  = trim($_POST['descripcion_larga'] ?? '');
        $ubicacion          = trim($_POST['ubicacion'] ?? '');
        $mapa               = trim($_POST['mapa'] ?? '');

        // Subida de imagen
        $imagen = '';
        if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/propiedades/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $ext       = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $filename  = uniqid('prop_', true) . '.' . strtolower($ext);
            $filepath  = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $filepath)) {
                $imagen = 'uploads/propiedades/' . $filename; // ruta pública
            }
        }

        // INSERT usando nombres reales
        $sql = "INSERT INTO propiedades
                  (titulo, descripcion_breve, precio, tipo, destacada, agente_id,
                   imagen_destacada, descripcion_larga, ubicacion, mapa)
                VALUES
                  (:titulo, :descripcion_breve, :precio, :tipo, :destacada, :agente_id,
                   :imagen_destacada, :descripcion_larga, :ubicacion, :mapa)";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':descripcion_breve', $descripcion_breve);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindValue(':destacada', $destacada, PDO::PARAM_INT);
        $stmt->bindValue(':agente_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':imagen_destacada', $imagen);
        $stmt->bindParam(':descripcion_larga', $descripcion_larga);
        $stmt->bindParam(':ubicacion', $ubicacion);
        $stmt->bindParam(':mapa', $mapa);

        if ($stmt->execute()) {
            $message = 'Propiedad agregada exitosamente';
        } else {
            $error = 'Error al agregar la propiedad';
        }
    }

    if ($action === 'edit') {
        $id                = (int)($_POST['id'] ?? 0);
        $titulo            = trim($_POST['titulo'] ?? '');
        $descripcion_breve = trim($_POST['descripcion'] ?? '');
        $precio            = (float)($_POST['precio'] ?? 0);
        $tipo              = $_POST['tipo'] ?? '';
        $destacada         = isset($_POST['destacada']) ? 1 : 0;
        $descripcion_larga = trim($_POST['descripcion_larga'] ?? '');
        $ubicacion         = trim($_POST['ubicacion'] ?? '');
        $mapa              = trim($_POST['mapa'] ?? '');

        // verificar propietario + traer imagen actual
        $qCheck = "SELECT imagen_destacada
                   FROM propiedades
                   WHERE id = :id AND agente_id = :agente_id";
        $stCheck = $db->prepare($qCheck);
        $stCheck->bindValue(':id', $id, PDO::PARAM_INT);
        $stCheck->bindValue(':agente_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stCheck->execute();

        if ($stCheck->rowCount() === 0) {
            $error = 'No tienes permisos para editar esta propiedad';
        } else {
            $current = $stCheck->fetch(PDO::FETCH_ASSOC);
            $imagen = $current['imagen_destacada'] ?? '';

            // Nueva imagen (opcional)
            if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/propiedades/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                $ext      = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('prop_', true) . '.' . strtolower($ext);
                $filepath = $upload_dir . $filename;

                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $filepath)) {
                    // borrar la anterior si existe
                    if (!empty($imagen) && file_exists('../' . $imagen)) {
                        @unlink('../' . $imagen);
                    }
                    $imagen = 'uploads/propiedades/' . $filename;
                }
            }

            // UPDATE con nombres reales
            $sql = "UPDATE propiedades SET
                        titulo = :titulo,
                        descripcion_breve = :descripcion_breve,
                        precio = :precio,
                        tipo = :tipo,
                        destacada = :destacada,
                        imagen_destacada = :imagen_destacada,
                        descripcion_larga = :descripcion_larga,
                        ubicacion = :ubicacion,
                        mapa = :mapa
                    WHERE id = :id AND agente_id = :agente_id";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':descripcion_breve', $descripcion_breve);
            $stmt->bindParam(':precio', $precio);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindValue(':destacada', $destacada, PDO::PARAM_INT);
            $stmt->bindParam(':imagen_destacada', $imagen);
            $stmt->bindParam(':descripcion_larga', $descripcion_larga);
            $stmt->bindParam(':ubicacion', $ubicacion);
            $stmt->bindParam(':mapa', $mapa);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':agente_id', $_SESSION['user_id'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                $message = 'Propiedad actualizada exitosamente';
            } else {
                $error = 'Error al actualizar la propiedad';
            }
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);

        // verificar propietario + traer imagen
        $qCheck = "SELECT imagen_destacada
                   FROM propiedades
                   WHERE id = :id AND agente_id = :agente_id";
        $stCheck = $db->prepare($qCheck);
        $stCheck->bindValue(':id', $id, PDO::PARAM_INT);
        $stCheck->bindValue(':agente_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stCheck->execute();

        if ($stCheck->rowCount() === 0) {
            $error = 'No tienes permisos para eliminar esta propiedad';
        } else {
            $prop = $stCheck->fetch(PDO::FETCH_ASSOC);

            $sql = "DELETE FROM propiedades WHERE id = :id AND agente_id = :agente_id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':agente_id', $_SESSION['user_id'], PDO::PARAM_INT);

            if ($stmt->execute()) {
                // borrar imagen física
                if (!empty($prop['imagen_destacada']) && file_exists('../' . $prop['imagen_destacada'])) {
                    @unlink('../' . $prop['imagen_destacada']);
                }
                $message = 'Propiedad eliminada exitosamente';
            } else {
                $error = 'Error al eliminar la propiedad';
            }
        }
    }
}

// ========== QUERIES PARA VISTA ==========

// Lista de propiedades del agente (alias imagen)
$qList = "SELECT
            id, titulo, tipo, precio, ubicacion, destacada,
            COALESCE(imagen_destacada,'') AS imagen
          FROM propiedades
          WHERE agente_id = :agente_id
          ORDER BY fecha_creacion DESC";
$stList = $db->prepare($qList);
$stList->bindValue(':agente_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stList->execute();
$propiedades = $stList->fetchAll(PDO::FETCH_ASSOC);

// Si se está editando, obtener datos (alias imagen)
$editing_property = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $qEdit = "SELECT
                id, titulo, tipo, precio, ubicacion, destacada, mapa,
                descripcion_breve AS descripcion,  -- para rellenar tu textarea 'descripcion'
                descripcion_larga,
                COALESCE(imagen_destacada,'') AS imagen
              FROM propiedades
              WHERE id = :id AND agente_id = :agente_id";
    $stEdit = $db->prepare($qEdit);
    $stEdit->bindValue(':id', $edit_id, PDO::PARAM_INT);
    $stEdit->bindValue(':agente_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stEdit->execute();
    if ($stEdit->rowCount() > 0) {
        $editing_property = $stEdit->fetch(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Propiedades - UTN Real Estate</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="admin-container">
    <nav class="admin-nav">
        <div class="nav-header">
            <h2>Panel Agente</h2>
            <p>Bienvenido, <?= htmlspecialchars($_SESSION['nombre']); ?></p>
        </div>
        <ul class="nav-menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="propiedades.php" class="active"><i class="fas fa-building"></i> Mis Propiedades</a></li>
            <li><a href="perfil.php"><i class="fas fa-user"></i> Mi Perfil</a></li>
            <li><a href="../index.php"><i class="fas fa-globe"></i> Ver Sitio Web</a></li>
            <li><a href="../includes/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        </ul>
    </nav>

    <main class="admin-content">
        <div class="admin-header">
            <h1>Gestión de Mis Propiedades</h1>
        </div>

        <?php if ($message): ?><div class="alert alert-success"><?= $message; ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?= $error; ?></div><?php endif; ?>

        <!-- Formulario -->
        <div class="form-section">
            <h2><?= $editing_property ? 'Editar Propiedad' : 'Agregar Nueva Propiedad'; ?></h2>
            <form method="POST" enctype="multipart/form-data" class="admin-form">
                <input type="hidden" name="action" value="<?= $editing_property ? 'edit' : 'add'; ?>">
                <?php if ($editing_property): ?>
                    <input type="hidden" name="id" value="<?= (int)$editing_property['id']; ?>">
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group">
                        <label for="titulo">Título:</label>
                        <input type="text" id="titulo" name="titulo" required
                               value="<?= $editing_property ? htmlspecialchars($editing_property['titulo']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="precio">Precio:</label>
                        <input type="number" id="precio" name="precio" step="0.01" min="0" required
                               value="<?= $editing_property ? (float)$editing_property['precio'] : ''; ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="tipo">Tipo:</label>
                        <select id="tipo" name="tipo" required>
                            <option value="venta"    <?= ($editing_property && $editing_property['tipo'] === 'venta') ? 'selected' : ''; ?>>Venta</option>
                            <option value="alquiler" <?= ($editing_property && $editing_property['tipo'] === 'alquiler') ? 'selected' : ''; ?>>Alquiler</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ubicacion">Ubicación:</label>
                        <input type="text" id="ubicacion" name="ubicacion"
                               value="<?= $editing_property ? htmlspecialchars($editing_property['ubicacion']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción Breve:</label>
                    <textarea id="descripcion" name="descripcion" required><?= $editing_property ? htmlspecialchars($editing_property['descripcion']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="descripcion_larga">Descripción Completa:</label>
                    <textarea id="descripcion_larga" name="descripcion_larga" rows="5"><?= $editing_property ? htmlspecialchars($editing_property['descripcion_larga']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="mapa">URL del Mapa:</label>
                    <input type="url" id="mapa" name="mapa"
                           value="<?= $editing_property ? htmlspecialchars($editing_property['mapa']) : ''; ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="imagen">Imagen:</label>
                        <input type="file" id="imagen" name="imagen" accept="image/*">
                        <?php if ($editing_property && !empty($editing_property['imagen'])): ?>
                            <div style="margin-top:10px">
                                <img src="../<?= htmlspecialchars($editing_property['imagen']); ?>" alt="Imagen actual" style="max-width:200px;height:auto;">
                                <p><small>Imagen actual</small></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group" style="display:flex;align-items:center">
                        <label>
                            <input type="checkbox" name="destacada" value="1"
                                <?= ($editing_property && !empty($editing_property['destacada'])) ? 'checked' : ''; ?>>
                            Marcar como destacada
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?= $editing_property ? 'Actualizar Propiedad' : 'Agregar Propiedad'; ?>
                    </button>
                    <?php if ($editing_property): ?>
                        <a href="propiedades.php" class="btn btn-secondary">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Lista -->
        <div class="table-section">
            <h2>Mis Propiedades</h2>
            <div class="table-container">
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Precio</th>
                        <th>Ubicación</th>
                        <th>Destacada</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($propiedades)): ?>
                        <tr><td colspan="7" style="text-align:center;">No tienes propiedades registradas</td></tr>
                    <?php else: ?>
                        <?php foreach ($propiedades as $p): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($p['imagen'])): ?>
                                        <img src="../<?= htmlspecialchars($p['imagen']); ?>"
                                             alt="Propiedad" style="width:60px;height:60px;object-fit:cover;border-radius:5px;">
                                    <?php else: ?>
                                        <div style="width:60px;height:60px;background:#ddd;border-radius:5px;display:flex;align-items:center;justify-content:center;">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($p['titulo']); ?></td>
                                <td>
                                    <span class="badge <?= ($p['tipo'] === 'venta') ? 'badge-success' : 'badge-info'; ?>">
                                        <?= ucfirst($p['tipo']); ?>
                                    </span>
                                </td>
                                <td>$<?= number_format((float)$p['precio']); ?></td>
                                <td><?= htmlspecialchars($p['ubicacion'] ?? ''); ?></td>
                                <td>
                                    <?php if (!empty($p['destacada'])): ?>
                                        <span class="badge badge-warning">Destacada</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Normal</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="propiedades.php?edit=<?= (int)$p['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" style="display:inline" onsubmit="return confirm('¿Estás seguro de eliminar esta propiedad?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$p['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>

