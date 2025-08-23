<?php
require_once '../config/database.php';
require_once '../includes/session.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

$mensaje = '';
$es_admin = $_SESSION['privilegio'] == 'administrador';

// Procesar acciones
if (isset($_GET['accion'])) {
    if ($_GET['accion'] == 'eliminar' && isset($_GET['id'])) {
        try {
            $where_clause = $es_admin ? "" : " AND agente_id = " . $_SESSION['user_id'];
            $query = "DELETE FROM propiedades WHERE id = :id" . $where_clause;
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $_GET['id']);
            if ($stmt->execute()) {
                $mensaje = '<div class="alert alert-success">Propiedad eliminada correctamente</div>';
            }
        } catch (Exception $e) {
            $mensaje = '<div class="alert alert-error">Error: ' . $e->getMessage() . '</div>';
        }
    }
}

// Procesar formulario
if ($_POST) {
    try {
        $imagen_destacada = $_POST['imagen_destacada_actual'] ?? 'uploads/casa_default.jpg';
        
        // Create uploads directory if it doesn't exist
        if (!file_exists('../uploads')) {
            mkdir('../uploads', 0777, true);
        }
        
        // Handle property image upload
        if (isset($_FILES['imagen_destacada']) && $_FILES['imagen_destacada']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['imagen_destacada']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = 'propiedad_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['imagen_destacada']['tmp_name'], '../uploads/' . $new_filename)) {
                    $imagen_destacada = 'uploads/' . $new_filename;
                    
                    // Also save to images table for gallery
                    $query_img = "INSERT INTO imagenes_sitio (tipo, nombre_archivo, ruta, descripcion) VALUES (?, ?, ?, ?)";
                    $stmt_img = $db->prepare($query_img);
                    $stmt_img->execute(['propiedad', $new_filename, $imagen_destacada, $_POST['titulo']]);
                }
            }
        }
        
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Actualizar propiedad
            $where_clause = $es_admin ? "" : " AND agente_id = " . $_SESSION['user_id'];
            $query = "UPDATE propiedades SET tipo = :tipo, destacada = :destacada, titulo = :titulo, 
                      descripcion_breve = :descripcion_breve, precio = :precio, descripcion_larga = :descripcion_larga,
                      ubicacion = :ubicacion, imagen_destacada = :imagen_destacada WHERE id = :id" . $where_clause;
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $_POST['id']);
            $stmt->bindParam(':imagen_destacada', $imagen_destacada);
        } else {
            // Crear nueva propiedad
            $query = "INSERT INTO propiedades (tipo, destacada, titulo, descripcion_breve, precio, agente_id, 
                      descripcion_larga, ubicacion, imagen_destacada) 
                      VALUES (:tipo, :destacada, :titulo, :descripcion_breve, :precio, :agente_id, 
                      :descripcion_larga, :ubicacion, :imagen_destacada)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':agente_id', $_SESSION['user_id']);
            $stmt->bindParam(':imagen_destacada', $imagen_destacada);
        }
        
        $stmt->bindParam(':tipo', $_POST['tipo']);
        $stmt->bindParam(':destacada', $_POST['destacada']);
        $stmt->bindParam(':titulo', $_POST['titulo']);
        $stmt->bindParam(':descripcion_breve', $_POST['descripcion_breve']);
        $stmt->bindParam(':precio', $_POST['precio']);
        $stmt->bindParam(':descripcion_larga', $_POST['descripcion_larga']);
        $stmt->bindParam(':ubicacion', $_POST['ubicacion']);
        
        if ($stmt->execute()) {
            $mensaje = '<div class="alert alert-success">Propiedad guardada correctamente</div>';
        }
    } catch (Exception $e) {
        $mensaje = '<div class="alert alert-error">Error: ' . $e->getMessage() . '</div>';
    }
}

// Obtener propiedades
$where_clause = $es_admin ? "" : " WHERE agente_id = " . $_SESSION['user_id'];
$query = "SELECT p.*, u.nombre as agente_nombre FROM propiedades p 
          LEFT JOIN usuarios u ON p.agente_id = u.id" . $where_clause . " 
          ORDER BY p.fecha_creacion DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$propiedades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener propiedad para editar
$propiedad_editar = null;
if (isset($_GET['editar'])) {
    $where_clause = $es_admin ? "" : " AND agente_id = " . $_SESSION['user_id'];
    $query = "SELECT * FROM propiedades WHERE id = :id" . $where_clause;
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['editar']);
    $stmt->execute();
    $propiedad_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Propiedades - UTN Real Estate</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-panel">
        <div class="admin-container">
            <div class="admin-header">
                <h1>Gestionar Propiedades</h1>
                <div class="admin-nav">
                    <a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a>
                    <?php if ($es_admin): ?>
                        <a href="personalizar.php"><i class="fas fa-palette"></i> Personalizar Página</a>
                        <a href="usuarios.php"><i class="fas fa-users"></i> Gestionar Usuarios</a>
                        <a href="galeria.php"><i class="fas fa-images"></i> Galería de Imágenes</a>
                    <?php endif; ?>
                    <a href="propiedades.php"><i class="fas fa-building"></i> Propiedades</a>
                    <a href="perfil.php"><i class="fas fa-user"></i> Mi Perfil</a>
                    <a href="../includes/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                </div>
            </div>
            
            <div class="admin-content">
                <?php echo $mensaje; ?>
                
                <div class="form-section">
                    <h3><i class="fas fa-plus"></i> <?php echo $propiedad_editar ? 'Editar Propiedad' : 'Agregar Nueva Propiedad'; ?></h3>
                    
                    <!-- Adding enctype for file uploads -->
                    <form method="POST" enctype="multipart/form-data">
                        <?php if ($propiedad_editar): ?>
                            <input type="hidden" name="id" value="<?php echo $propiedad_editar['id']; ?>">
                            <input type="hidden" name="imagen_destacada_actual" value="<?php echo $propiedad_editar['imagen_destacada']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label>Tipo:</label>
                            <select name="tipo" required>
                                <option value="venta" <?php echo ($propiedad_editar && $propiedad_editar['tipo'] == 'venta') ? 'selected' : ''; ?>>Venta</option>
                                <option value="alquiler" <?php echo ($propiedad_editar && $propiedad_editar['tipo'] == 'alquiler') ? 'selected' : ''; ?>>Alquiler</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>¿Es destacada?:</label>
                            <select name="destacada" required>
                                <option value="0" <?php echo ($propiedad_editar && $propiedad_editar['destacada'] == 0) ? 'selected' : ''; ?>>No</option>
                                <option value="1" <?php echo ($propiedad_editar && $propiedad_editar['destacada'] == 1) ? 'selected' : ''; ?>>Sí</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Título:</label>
                            <input type="text" name="titulo" value="<?php echo $propiedad_editar ? htmlspecialchars($propiedad_editar['titulo']) : ''; ?>" required>
                        </div>
                        
                        <!-- Adding image upload field for properties -->
                        <div class="form-group">
                            <label>Imagen Destacada:</label>
                            <?php if ($propiedad_editar && !empty($propiedad_editar['imagen_destacada']) && file_exists('../' . $propiedad_editar['imagen_destacada'])): ?>
                                <img src="../<?php echo $propiedad_editar['imagen_destacada']; ?>" alt="Imagen actual" class="image-preview">
                            <?php endif; ?>
                            <input type="file" name="imagen_destacada" accept="image/*">
                            <small>Formatos permitidos: JPG, PNG, GIF. Deja vacío para mantener la imagen actual.</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Descripción Breve:</label>
                            <textarea name="descripcion_breve" rows="3" required><?php echo $propiedad_editar ? htmlspecialchars($propiedad_editar['descripcion_breve']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Precio:</label>
                            <input type="number" name="precio" step="0.01" value="<?php echo $propiedad_editar ? $propiedad_editar['precio'] : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Descripción Larga:</label>
                            <textarea name="descripcion_larga" rows="5"><?php echo $propiedad_editar ? htmlspecialchars($propiedad_editar['descripcion_larga']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Ubicación:</label>
                            <input type="text" name="ubicacion" value="<?php echo $propiedad_editar ? htmlspecialchars($propiedad_editar['ubicacion']) : ''; ?>">
                        </div>
                        
                        <button type="submit" class="btn">
                            <i class="fas fa-save"></i> <?php echo $propiedad_editar ? 'Actualizar' : 'Agregar'; ?> Propiedad
                        </button>
                        <?php if ($propiedad_editar): ?>
                            <a href="propiedades.php" class="btn">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <h3><i class="fas fa-list"></i> <?php echo $es_admin ? 'Todas las Propiedades' : 'Mis Propiedades'; ?></h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Precio</th>
                            <th>Destacada</th>
                            <?php if ($es_admin): ?>
                                <th>Agente</th>
                            <?php endif; ?>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($propiedades as $propiedad): ?>
                        <tr>
                            <!-- Adding image preview in properties table -->
                            <td>
                                <?php if (!empty($propiedad['imagen_destacada']) && file_exists('../' . $propiedad['imagen_destacada'])): ?>
                                    <img src="../<?php echo $propiedad['imagen_destacada']; ?>" alt="<?php echo htmlspecialchars($propiedad['titulo']); ?>" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    <div style="width: 60px; height: 40px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image" style="color: #ccc;"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($propiedad['titulo']); ?></td>
                            <td><?php echo ucfirst($propiedad['tipo']); ?></td>
                            <td>$<?php echo number_format($propiedad['precio']); ?></td>
                            <td><?php echo $propiedad['destacada'] ? 'Sí' : 'No'; ?></td>
                            <?php if ($es_admin): ?>
                                <td><?php echo htmlspecialchars($propiedad['agente_nombre']); ?></td>
                            <?php endif; ?>
                            <td>
                                <a href="propiedades.php?editar=<?php echo $propiedad['id']; ?>" class="btn btn-sm">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="propiedades.php?accion=eliminar&id=<?php echo $propiedad['id']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('¿Está seguro de eliminar esta propiedad?')">
                                    <i class="fas fa-trash"></i> Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if (empty($propiedades)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No hay propiedades registradas. Agrega tu primera propiedad usando el formulario de arriba.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
