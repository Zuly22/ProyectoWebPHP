<?php
require_once '../config/database.php';
require_once '../includes/session.php';

requireAdmin();

$database = new Database();
$db = $database->getConnection();

$mensaje = '';

if ($_POST && isset($_POST['action'])) {
    if ($_POST['action'] == 'upload' && isset($_FILES['imagen'])) {
        try {
            if (!file_exists('../uploads')) {
                mkdir('../uploads', 0777, true);
            }
            
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['imagen']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = 'gallery_' . time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['imagen']['tmp_name'], '../uploads/' . $new_filename)) {
                    $query = "INSERT INTO imagenes_sitio (tipo, nombre_archivo, ruta, descripcion) VALUES (?, ?, ?, ?)";
                    $stmt = $db->prepare($query);
                    $stmt->execute(['galeria', $new_filename, 'uploads/' . $new_filename, $_POST['descripcion']]);
                    $mensaje = '<div class="alert alert-success">Imagen subida correctamente</div>';
                }
            } else {
                $mensaje = '<div class="alert alert-error">Formato de archivo no permitido</div>';
            }
        } catch (Exception $e) {
            $mensaje = '<div class="alert alert-error">Error: ' . $e->getMessage() . '</div>';
        }
    }
    
    if ($_POST['action'] == 'delete' && isset($_POST['imagen_id'])) {
        try {
            $query = "SELECT ruta FROM imagenes_sitio WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$_POST['imagen_id']]);
            $imagen = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($imagen && file_exists('../' . $imagen['ruta'])) {
                unlink('../' . $imagen['ruta']);
            }
            
            $query = "DELETE FROM imagenes_sitio WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$_POST['imagen_id']]);
            $mensaje = '<div class="alert alert-success">Imagen eliminada correctamente</div>';
        } catch (Exception $e) {
            $mensaje = '<div class="alert alert-error">Error: ' . $e->getMessage() . '</div>';
        }
    }
}

$query = "SELECT * FROM imagenes_sitio ORDER BY fecha_subida DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería de Imágenes - UTN Real Estate</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .gallery-item {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .gallery-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .gallery-item-info {
            padding: 15px;
        }
        .gallery-item-actions {
            padding: 10px 15px;
            background: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .upload-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="admin-panel">
        <div class="admin-container">
            <div class="admin-header">
                <h1>Galería de Imágenes</h1>
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
            
            <div class="admin-content">
                <?php echo $mensaje; ?>
                
                <div class="upload-section">
                    <h3><i class="fas fa-upload"></i> Subir Nueva Imagen</h3>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="upload">
                        <div class="form-group">
                            <label>Seleccionar Imagen:</label>
                            <input type="file" name="imagen" accept="image/*" required>
                        </div>
                        <div class="form-group">
                            <label>Descripción:</label>
                            <input type="text" name="descripcion" placeholder="Descripción de la imagen">
                        </div>
                        <button type="submit" class="btn"><i class="fas fa-upload"></i> Subir Imagen</button>
                    </form>
                </div>
                
                <h3>Imágenes Disponibles (<?php echo count($imagenes); ?>)</h3>
                
                <div class="gallery-grid">
                    <?php foreach ($imagenes as $imagen): ?>
                    <div class="gallery-item">
                        <img src="../<?php echo $imagen['ruta']; ?>" alt="<?php echo htmlspecialchars($imagen['descripcion']); ?>">
                        <div class="gallery-item-info">
                            <h4><?php echo htmlspecialchars($imagen['nombre_archivo']); ?></h4>
                            <p><strong>Tipo:</strong> <?php echo ucfirst($imagen['tipo']); ?></p>
                            <p><strong>Descripción:</strong> <?php echo htmlspecialchars($imagen['descripcion']); ?></p>
                            <p><strong>Subida:</strong> <?php echo date('d/m/Y H:i', strtotime($imagen['fecha_subida'])); ?></p>
                        </div>
                        <div class="gallery-item-actions">
                            <small>ID: <?php echo $imagen['id']; ?></small>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de eliminar esta imagen?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="imagen_id" value="<?php echo $imagen['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($imagenes)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No hay imágenes en la galería. Sube tu primera imagen usando el formulario de arriba.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
