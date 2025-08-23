<?php
require_once 'config/database.php';
require_once 'includes/session.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    
    if (empty($usuario) || empty($contrasena)) {
        $error = 'Por favor complete todos los campos';
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM usuarios WHERE usuario = :usuario";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar contraseña (para el admin por defecto usamos verificación simple)
            if ($usuario === 'Admin' && $contrasena === '123') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['privilegio'] = $user['privilegio'];
                $_SESSION['nombre'] = $user['nombre'];
                
                header("Location: admin/dashboard.php");
                exit();
            } elseif (password_verify($contrasena, $user['contrasena'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['privilegio'] = $user['privilegio'];
                $_SESSION['nombre'] = $user['nombre'];
                
                if ($user['privilegio'] === 'administrador') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: agente/dashboard.php");
                }
                exit();
            } else {
                $error = 'Usuario o contraseña incorrectos';
            }
        } else {
            $error = 'Usuario o contraseña incorrectos';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UTN Real Estate</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <form class="login-form" method="POST">
            <h2>Iniciar Sesión</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="usuario">Usuario:</label>
                <input type="text" id="usuario" name="usuario" required>
            </div>
            
            <div class="form-group">
                <label for="contrasena">Contraseña:</label>
                <input type="password" id="contrasena" name="contrasena" required>
            </div>
            
            <button type="submit" class="btn" style="width: 100%;">Ingresar</button>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="index.php">Volver al inicio</a>
            </div>
        </form>
    </div>
</body>
</html>

