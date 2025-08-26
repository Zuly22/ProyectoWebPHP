<?php
require_once '../config/database.php';
require_once '../includes/session.php';

requireAdmin();

$database = new Database();
$db = $database->getConnection();

$mensaje = '';

if (isset($_GET['accion'])) {
    if ($_GET['accion'] == 'eliminar' && isset($_GET['id'])) {
        try {
            $query = "DELETE FROM usuarios WHERE id = :id AND id != 1"; 
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $_GET['id']);
            if ($stmt->execute()) {
                $mensaje = '<div class="alert alert-success">Usuario eliminado correctamente</div>';
            }
        } catch (Exception $e) {
            $mensaje = '<div class="alert alert-error">Error: ' . $e->getMessage() . '</div>';
        }
    }
}

if ($_POST) {
    try {
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            
            $query = "UPDATE usuarios SET nombre = :nombre, telefono = :telefono, correo = :correo, 
                      email = :email, usuario = :usuario, privilegio = :privilegio";
            
            if (!empty($_POST['contrasena'])) {
                $query .= ", contrasena = :contrasena";
            }
            
            $query .= " WHERE id = :id";
            $stmt = $db->prepare($query);
            
            if (!empty($_POST['contrasena'])) {
                $contrasena_hash = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
                $stmt->bindParam(':contrasena', $contrasena_hash);
            }
            
            $stmt->bindParam(':id', $_POST['id']);
        } else {
            
            $query = "INSERT INTO usuarios (nombre, telefono, correo, email, usuario, contrasena, privilegio) 
                      VALUES (:nombre, :telefono, :correo, :email, :usuario, :contrasena, :privilegio)";
            $stmt = $db->prepare($query);
            
            $contrasena_hash = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
            $stmt->bindParam(':contrasena', $contrasena_hash);
        }
        
        $stmt->bindParam(':nombre', $_POST['nombre']);
        $stmt->bindParam(':telefono', $_POST['telefono']);
        $stmt->bindParam(':correo', $_POST['correo']);
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->bindParam(':usuario', $_POST['usuario']);
        $stmt->bindParam(':privilegio', $_POST['privilegio']);
        
        if ($stmt->execute()) {
            $mensaje = '<div class="alert alert-success">Usuario guardado correctamente</div>';
        }
    } catch (Exception $e) {
        $mensaje = '<div class="alert alert-error">Error: ' . $e->getMessage() . '</div>';
    }
}

$query = "SELECT * FROM usuarios ORDER BY fecha_creacion DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$usuario_editar = null;
if (isset($_GET['editar'])) {
    $query = "SELECT * FROM usuarios WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['editar']);
    $stmt->execute();
    $usuario_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios - UTN Real Estate</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-panel">
        <div class="admin-container">
            <div class="admin-header">
                <h1>Gestionar Usuarios</h1>
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
                
                <h3><?php echo $usuario_editar ? 'Editar Usuario' : 'Crear Nuevo Usuario'; ?></h3>
                
                <form method="POST">
                    <?php if ($usuario_editar): ?>
                        <input type="hidden" name="id" value="<?php echo $usuario_editar['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Nombre:</label>
                        <input type="text" name="nombre" value="<?php echo $usuario_editar ? htmlspecialchars($usuario_editar['nombre']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Teléfono:</label>
                        <input type="text" name="telefono" value="<?php echo $usuario_editar ? htmlspecialchars($usuario_editar['telefono']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Correo:</label>
                        <input type="email" name="correo" value="<?php echo $usuario_editar ? htmlspecialchars($usuario_editar['correo']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" value="<?php echo $usuario_editar ? htmlspecialchars($usuario_editar['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Usuario:</label>
                        <input type="text" name="usuario" value="<?php echo $usuario_editar ? htmlspecialchars($usuario_editar['usuario']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Contraseña <?php echo $usuario_editar ? '(dejar vacío para mantener actual)' : ''; ?>:</label>
                        <input type="password" name="contrasena" <?php echo !$usuario_editar ? 'required' : ''; ?>>
                    </div>
                    
                    <div class="form-group">
                        <label>Privilegio:</label>
                        <select name="privilegio" required>
                            <option value="administrador" <?php echo ($usuario_editar && $usuario_editar['privilegio'] == 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                            <option value="agente_ventas" <?php echo ($usuario_editar && $usuario_editar['privilegio'] == 'agente_ventas') ? 'selected' : ''; ?>>Agente de Ventas</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn"><?php echo $usuario_editar ? 'Actualizar' : 'Crear'; ?> Usuario</button>
                    <?php if ($usuario_editar): ?>
                        <a href="usuarios.php" class="btn">Cancelar</a>
                    <?php endif; ?>
                </form>
                
                <h3>Lista de Usuarios</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Usuario</th>
                            <th>Correo</th>
                            <th>Privilegio</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['usuario']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                            <td><?php echo ucfirst(str_replace('_', ' ', $usuario['privilegio'])); ?></td>
                            <td>
                                <a href="usuarios.php?editar=<?php echo $usuario['id']; ?>" class="btn">Editar</a>
                                <?php if ($usuario['id'] != 1): ?>
                                    <a href="usuarios.php?accion=eliminar&id=<?php echo $usuario['id']; ?>" 
                                       class="btn btn-danger" 
                                       onclick="return confirm('¿Está seguro de eliminar este usuario?')">Eliminar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
