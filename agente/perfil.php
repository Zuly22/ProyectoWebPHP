<?php
require_once '../config/database.php';
require_once '../includes/session.php';

if (!isset($_SESSION['user_id']) || $_SESSION['privilegio'] !== 'agente_ventas') {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

$query = "SELECT * FROM usuarios WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$usuario_actual = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $email = $_POST['email'] ?? '';
    $nueva_contrasena = $_POST['nueva_contrasena'] ?? '';
    $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';
    
    if (empty($nombre) || empty($telefono) || empty($email)) {
        $error = 'Por favor complete todos los campos obligatorios';
    } else {
        if (!empty($nueva_contrasena)) {
            if ($nueva_contrasena !== $confirmar_contrasena) {
                $error = 'Las contraseñas no coinciden';
            } elseif (strlen($nueva_contrasena) < 6) {
                $error = 'La contraseña debe tener al menos 6 caracteres';
            } else {
                $contrasena_hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
                $query = "UPDATE usuarios SET nombre = :nombre, telefono = :telefono, email = :email, contrasena = :contrasena WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':telefono', $telefono);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':contrasena', $contrasena_hash);
                $stmt->bindParam(':id', $_SESSION['user_id']);
            }
        } else {
            $query = "UPDATE usuarios SET nombre = :nombre, telefono = :telefono, email = :email WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $_SESSION['user_id']);
        }
        
        if (!$error && $stmt->execute()) {
            $_SESSION['nombre'] = $nombre; 
            $message = 'Perfil actualizado exitosamente';
            
            $stmt_reload = $db->prepare("SELECT * FROM usuarios WHERE id = :id");
            $stmt_reload->bindParam(':id', $_SESSION['user_id']);
            $stmt_reload->execute();
            $usuario_actual = $stmt_reload->fetch(PDO::FETCH_ASSOC);
        } elseif (!$error) {
            $error = 'Error al actualizar el perfil';
        }
    }
}
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
    <div class="admin-container">
        <nav class="admin-nav">
            <div class="nav-header">
                <h2>Panel Agente</h2>
                <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></p>
            </div>
            <ul class="nav-menu">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="propiedades.php"><i class="fas fa-building"></i> Mis Propiedades</a></li>
                <li><a href="perfil.php" class="active"><i class="fas fa-user"></i> Mi Perfil</a></li>
                <li><a href="../index.php"><i class="fas fa-globe"></i> Ver Sitio Web</a></li>
                <li><a href="../includes/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
            </ul>
        </nav>

        <main class="admin-content">
            <div class="admin-header">
                <h1>Mi Perfil</h1>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="form-section">
                <h2>Actualizar Información Personal</h2>
                <form method="POST" class="admin-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre Completo:</label>
                            <input type="text" id="nombre" name="nombre" required 
                                   value="<?php echo htmlspecialchars($usuario_actual['nombre']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="telefono">Teléfono:</label>
                            <input type="tel" id="telefono" name="telefono" required 
                                   value="<?php echo htmlspecialchars($usuario_actual['telefono']); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required 
                                   value="<?php echo htmlspecialchars($usuario_actual['email']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="usuario">Usuario:</label>
                            <input type="text" id="usuario" name="usuario" readonly 
                                   value="<?php echo htmlspecialchars($usuario_actual['usuario']); ?>"
                                   style="background-color: #f5f5f5;">
                            <small>El nombre de usuario no se puede cambiar</small>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Cambiar Contraseña (Opcional)</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nueva_contrasena">Nueva Contraseña:</label>
                                <input type="password" id="nueva_contrasena" name="nueva_contrasena" 
                                       minlength="6" placeholder="Dejar vacío para mantener la actual">
                            </div>

                            <div class="form-group">
                                <label for="confirmar_contrasena">Confirmar Nueva Contraseña:</label>
                                <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" 
                                       minlength="6" placeholder="Confirmar nueva contraseña">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Actualizar Perfil</button>
                    </div>
                </form>
            </div>

            <div class="info-section">
                <h2>Información de la Cuenta</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Tipo de Usuario:</strong>
                        <span class="badge badge-info">Agente de Ventas</span>
                    </div>
                    <div class="info-item">
                        <strong>Fecha de Registro:</strong>
                        <?php echo date('d/m/Y', strtotime($usuario_actual['fecha_creacion'])); ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('confirmar_contrasena').addEventListener('input', function() {
            const nueva = document.getElementById('nueva_contrasena').value;
            const confirmar = this.value;
            
            if (nueva !== '' && confirmar !== '' && nueva !== confirmar) {
                this.setCustomValidity('Las contraseñas no coinciden');
            } else {
                this.setCustomValidity('');
            }
        });

        document.getElementById('nueva_contrasena').addEventListener('input', function() {
            const confirmar = document.getElementById('confirmar_contrasena');
            if (this.value === '') {
                confirmar.value = '';
            }
            confirmar.dispatchEvent(new Event('input'));
        });
    </script>
</body>
</html>
