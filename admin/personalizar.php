<?php
require_once '../config/database.php';
require_once '../includes/session.php';

requireAdmin();

$database = new Database();
$db = $database->getConnection();

$mensaje = '';

// Procesar formulario
if ($_POST) {
    try {
        // Handle file uploads
        $imagen_banner = $_POST['imagen_banner_actual'];
        $imagen_quienes_somos = $_POST['imagen_quienes_somos_actual'];
        $logo_principal = $_POST['logo_principal_actual'];
        $logo_blanco = $_POST['logo_blanco_actual'];
        
        // Create uploads directory if it doesn't exist
        if (!file_exists('../uploads')) {
            mkdir('../uploads', 0777, true);
        }
        
        // Handle banner image upload
        if (isset($_FILES['imagen_banner']) && $_FILES['imagen_banner']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['imagen_banner']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = 'banner_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['imagen_banner']['tmp_name'], '../uploads/' . $new_filename)) {
                    $imagen_banner = 'uploads/' . $new_filename;
                }
            }
        }
        
        // Handle quienes somos image upload
        if (isset($_FILES['imagen_quienes_somos']) && $_FILES['imagen_quienes_somos']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['imagen_quienes_somos']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = 'quienes_somos_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['imagen_quienes_somos']['tmp_name'], '../uploads/' . $new_filename)) {
                    $imagen_quienes_somos = 'uploads/' . $new_filename;
                }
            }
        }
        
        // Handle logo principal upload
        if (isset($_FILES['logo_principal']) && $_FILES['logo_principal']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['logo_principal']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = 'logo_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['logo_principal']['tmp_name'], '../uploads/' . $new_filename)) {
                    $logo_principal = 'uploads/' . $new_filename;
                }
            }
        }
        
        // Handle logo blanco upload
        if (isset($_FILES['logo_blanco']) && $_FILES['logo_blanco']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['logo_blanco']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = 'logo_blanco_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['logo_blanco']['tmp_name'], '../uploads/' . $new_filename)) {
                    $logo_blanco = 'uploads/' . $new_filename;
                }
            }
        }

        $query = "UPDATE configuracion_sitio SET 
                  tema_color = :tema_color,
                  mensaje_banner = :mensaje_banner,
                  quienes_somos_texto = :quienes_somos_texto,
                  facebook_url = :facebook_url,
                  youtube_url = :youtube_url,
                  instagram_url = :instagram_url,
                  direccion = :direccion,
                  telefono_contacto = :telefono_contacto,
                  email_contacto = :email_contacto,
                  imagen_banner = :imagen_banner,
                  imagen_quienes_somos = :imagen_quienes_somos,
                  logo_principal = :logo_principal,
                  logo_blanco = :logo_blanco
                  WHERE id = 1";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':tema_color', $_POST['tema_color']);
        $stmt->bindParam(':mensaje_banner', $_POST['mensaje_banner']);
        $stmt->bindParam(':quienes_somos_texto', $_POST['quienes_somos_texto']);
        $stmt->bindParam(':facebook_url', $_POST['facebook_url']);
        $stmt->bindParam(':youtube_url', $_POST['youtube_url']);
        $stmt->bindParam(':instagram_url', $_POST['instagram_url']);
        $stmt->bindParam(':direccion', $_POST['direccion']);
        $stmt->bindParam(':telefono_contacto', $_POST['telefono_contacto']);
        $stmt->bindParam(':email_contacto', $_POST['email_contacto']);
        $stmt->bindParam(':imagen_banner', $imagen_banner);
        $stmt->bindParam(':imagen_quienes_somos', $imagen_quienes_somos);
        $stmt->bindParam(':logo_principal', $logo_principal);
        $stmt->bindParam(':logo_blanco', $logo_blanco);
        
        if ($stmt->execute()) {
            $mensaje = '<div class="alert alert-success">Configuración actualizada correctamente</div>';
        }
    } catch (Exception $e) {
        $mensaje = '<div class="alert alert-error">Error: ' . $e->getMessage() . '</div>';
    }
}

// Obtener configuración actual
$query = "SELECT * FROM configuracion_sitio LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute();
$config = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalizar Página - UTN Real Estate</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .image-preview {
            max-width: 200px;
            max-height: 150px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-section {
            background: #f8f9fa;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        .form-section h3 {
            margin-top: 0;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="admin-panel">
        <div class="admin-container">
            <div class="admin-header">
                <h1>Personalizar Página</h1>
                <div class="admin-nav">
                    <a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a>
                    <a href="personalizar.php"><i class="fas fa-palette"></i> Personalizar Página</a>
                    <a href="usuarios.php"><i class="fas fa-users"></i> Gestionar Usuarios</a>
                    <a href="propiedades.php"><i class="fas fa-building"></i> Propiedades</a>
                    <a href="galeria.php"><i class="fas fa-images"></i> Galería de Imágenes</a>
                    <a href="../includes/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                </div>
            </div>
            
            <div class="admin-content">
                <?php echo $mensaje; ?>
                
                <!-- Adding enctype for file uploads -->
                <form method="POST" enctype="multipart/form-data">
                    
                    <div class="form-section">
                        <h3><i class="fas fa-palette"></i> Configuración Visual</h3>
                        
                        <div class="form-group">
                            <label>Tema de Color:</label>
                            <select name="tema_color" required>
                                <option value="azul" <?php echo ($config['tema_color'] == 'azul') ? 'selected' : ''; ?>>Azul</option>
                                <option value="amarillo_gris" <?php echo ($config['tema_color'] == 'amarillo_gris') ? 'selected' : ''; ?>>Amarillo y Gris</option>
                                <option value="blanco_gris" <?php echo ($config['tema_color'] == 'blanco_gris') ? 'selected' : ''; ?>>Blanco y Gris</option>
                            </select>
                        </div>
                    </div>

                    <!-- Adding image upload sections -->
                    <div class="form-section">
                        <h3><i class="fas fa-images"></i> Imágenes del Sitio</h3>
                        
                        <div class="form-group">
                            <label>Logo Principal:</label>
                            <?php if (!empty($config['logo_principal'])): ?>
                                <img src="../<?php echo $config['logo_principal']; ?>" alt="Logo actual" class="image-preview">
                            <?php endif; ?>
                            <input type="file" name="logo_principal" accept="image/*">
                            <input type="hidden" name="logo_principal_actual" value="<?php echo $config['logo_principal']; ?>">
                            <small>Formatos permitidos: JPG, PNG, GIF</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Logo Blanco:</label>
                            <?php if (!empty($config['logo_blanco'])): ?>
                                <img src="../<?php echo $config['logo_blanco']; ?>" alt="Logo blanco actual" class="image-preview">
                            <?php endif; ?>
                            <input type="file" name="logo_blanco" accept="image/*">
                            <input type="hidden" name="logo_blanco_actual" value="<?php echo $config['logo_blanco']; ?>">
                            <small>Formatos permitidos: JPG, PNG, GIF</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Imagen del Banner Principal:</label>
                            <?php if (!empty($config['imagen_banner'])): ?>
                                <img src="../<?php echo $config['imagen_banner']; ?>" alt="Banner actual" class="image-preview">
                            <?php endif; ?>
                            <input type="file" name="imagen_banner" accept="image/*">
                            <input type="hidden" name="imagen_banner_actual" value="<?php echo $config['imagen_banner']; ?>">
                            <small>Formatos permitidos: JPG, PNG, GIF</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Imagen de Quienes Somos:</label>
                            <?php if (!empty($config['imagen_quienes_somos'])): ?>
                                <img src="../<?php echo $config['imagen_quienes_somos']; ?>" alt="Quienes somos actual" class="image-preview">
                            <?php endif; ?>
                            <input type="file" name="imagen_quienes_somos" accept="image/*">
                            <input type="hidden" name="imagen_quienes_somos_actual" value="<?php echo $config['imagen_quienes_somos']; ?>">
                            <small>Formatos permitidos: JPG, PNG, GIF</small>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3><i class="fas fa-edit"></i> Contenido de la Página</h3>
                        
                        <div class="form-group">
                            <label>Mensaje del Banner:</label>
                            <textarea name="mensaje_banner" rows="3" required><?php echo htmlspecialchars($config['mensaje_banner']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Texto Quienes Somos:</label>
                            <textarea name="quienes_somos_texto" rows="5" required><?php echo htmlspecialchars($config['quienes_somos_texto']); ?></textarea>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3><i class="fas fa-share-alt"></i> Redes Sociales</h3>
                        
                        <div class="form-group">
                            <label>URL Facebook:</label>
                            <input type="url" name="facebook_url" value="<?php echo htmlspecialchars($config['facebook_url']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>URL YouTube:</label>
                            <input type="url" name="youtube_url" value="<?php echo htmlspecialchars($config['youtube_url']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>URL Instagram:</label>
                            <input type="url" name="instagram_url" value="<?php echo htmlspecialchars($config['instagram_url']); ?>">
                        </div>
                    </div>

                    <div class="form-section">
                        <h3><i class="fas fa-address-book"></i> Información de Contacto</h3>
                        
                        <div class="form-group">
                            <label>Dirección:</label>
                            <input type="text" name="direccion" value="<?php echo htmlspecialchars($config['direccion']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Teléfono de Contacto:</label>
                            <input type="text" name="telefono_contacto" value="<?php echo htmlspecialchars($config['telefono_contacto']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Email de Contacto:</label>
                            <input type="email" name="email_contacto" value="<?php echo htmlspecialchars($config['email_contacto']); ?>" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn"><i class="fas fa-save"></i> Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
