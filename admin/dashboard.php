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
        $query = "UPDATE configuracion_sitio SET 
                  tema_color = :tema_color,
                  mensaje_banner = :mensaje_banner,
                  quienes_somos_texto = :quienes_somos_texto,
                  facebook_url = :facebook_url,
                  youtube_url = :youtube_url,
                  instagram_url = :instagram_url,
                  direccion = :direccion,
                  telefono_contacto = :telefono_contacto,
                  email_contacto = :email_contacto
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
                    <a href="../includes/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                </div>
            </div>
            
            <div class="admin-content">
                <?php echo $mensaje; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Tema de Color:</label>
                        <select name="tema_color" required>
                            <option value="azul" <?php echo ($config['tema_color'] == 'azul') ? 'selected' : ''; ?>>Azul</option>
                            <option value="amarillo_gris" <?php echo ($config['tema_color'] == 'amarillo_gris') ? 'selected' : ''; ?>>Amarillo y Gris</option>
                            <option value="blanco_gris" <?php echo ($config['tema_color'] == 'blanco_gris') ? 'selected' : ''; ?>>Blanco y Gris</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Mensaje del Banner:</label>
                        <textarea name="mensaje_banner" rows="3" required><?php echo htmlspecialchars($config['mensaje_banner']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Texto Quienes Somos:</label>
                        <textarea name="quienes_somos_texto" rows="5" required><?php echo htmlspecialchars($config['quienes_somos_texto']); ?></textarea>
                    </div>
                    
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
                    
                    <button type="submit" class="btn">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

