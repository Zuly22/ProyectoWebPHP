<?php
require_once '../config/database.php';
require_once '../includes/session.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

$mensaje = '';

// Procesar formulario
if ($_POST) {
    try {
        $query = "UPDATE usuarios SET nombre = :nombre, telefono = :telefono, correo = :correo, email = :email";
        
        if (!empty($_POST['contrasena'])) {
            $query .= ", contrasena = :contrasena";
        }
        
        $query .= " WHERE id = :id";
        $stmt = $db->prepare($query);
        
        if (!empty($_POST['contrasena'])) {
            $contrasena_hash = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
            $stmt->bindParam(':contrasena', $contrasena_hash);
        }
        
        $stmt->bindParam(':nombre', $_POST['nombre']);
        $stmt->bindParam(':telefono', $_POST['telefono']);
        $stmt->bindParam(':correo', $_POST['correo']);
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->bindParam(':id', $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $_SESSION['nombre'] = $_POST['nombre'];
            $mensaje = '<div class="alert alert-success">Perfil actualizado correctamente</div>';
        }
    } catch (Exception $e) {
        $mensaje = '<div class="alert alert-error">Error: ' . $e->getMessage() . '</div>';
    }
}

// Obtener datos del usuario
$query = "SELECT * FROM usuarios WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - UTN Real Estate</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-panel">
        <div class="admin-container">
            <div class="admin-header">
                <h1>Mi Perfil</h1>
                <div class="admin-nav">
                    <a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a>
                    <?php if ($_SESSION['privilegio'] == 'administrador'): ?>
                        <a href="personalizar.php"><i class="fas fa-palette"></i> Personalizar Página</a>
                        <a href="usuarios.php"><i class="fas fa-users"></i> Gestionar Usuarios</a>
                    <?php endif; ?>
                    <a href="propiedades.php"><i class="fas fa-building"></i> Propiedades</a>
                    <a href="galeria.php"><i class="fas fa-images"></i> Galería de Imágenes</a>
                    <a href="perfil.php"><i class="fas fa-building"></i> Mi perfil</a>
                    <a href="../includes/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                </div>
            </div>
            
            <div class="admin-content">
                <?php echo $mensaje; ?>
                
                <h3>Actualizar Datos Personales</h3>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Nombre:</label>
                        <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Teléfono:</label>
                        <input type="text" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Correo:</label>
                        <input type="email" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Usuario:</label>
                        <input type="text" value="<?php echo htmlspecialchars($usuario['usuario']); ?>" disabled>
                        <small>El nombre de usuario no se puede cambiar</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Nueva Contraseña (dejar vacío para mantener actual):</label>
                        <input type="password" name="contrasena">
                    </div>
                    
                    <div class="form-group">
                        <label>Privilegio:</label>
                        <input type="text" value="<?php echo ucfirst(str_replace('_', ' ', $usuario['privilegio'])); ?>" disabled>
                    </div>
                    
                    <button type="submit" class="btn">Actualizar Perfil</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
