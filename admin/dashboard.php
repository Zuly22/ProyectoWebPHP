<?php
require_once '../config/database.php';
require_once '../includes/session.php';

requireAdmin();

$database = new Database();
$db = $database->getConnection();

$query = "SELECT COUNT(*) as total FROM propiedades";
$stmt = $db->prepare($query);
$stmt->execute();
$total_propiedades = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$query = "SELECT COUNT(*) as total FROM usuarios WHERE privilegio = 'agente_ventas'";
$stmt = $db->prepare($query);
$stmt->execute();
$total_agentes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - UTN Real Estate</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-panel">
        <div class="admin-container">
            <div class="admin-header">
                <h1>Panel Administrativo</h1>
                <p>Bienvenido, <?php echo $_SESSION['nombre']; ?></p>
                
                <div class="admin-nav">
                    <a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a>
                    <a href="../index.php"><i class="fas fa-globe"></i> Ver Sitio Web</a>
                    <a href="personalizar.php"><i class="fas fa-palette"></i> Personalizar Página</a>
                    <a href="usuarios.php"><i class="fas fa-users"></i> Gestionar Usuarios</a>
                    <a href="propiedades.php"><i class="fas fa-building"></i> Propiedades</a>
                    <a href="perfil.php"><i class="fas fa-user"></i> Mi Perfil</a>
                    <a href="../includes/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                </div>
            </div>
            
            <div class="admin-content">
                <h2>Resumen del Sistema</h2>
                
                <div class="properties-grid">
                    <div class="property-card">
                        <div class="property-info">
                            <h3>Total de Propiedades</h3>
                            <div class="property-price"><?php echo $total_propiedades; ?></div>
                        </div>
                    </div>
                    
                    <div class="property-card">
                        <div class="property-info">
                            <h3>Agentes de Ventas</h3>
                            <div class="property-price"><?php echo $total_agentes; ?></div>
                        </div>
                    </div>
                    
                    <div class="property-card">
                        <div class="property-info">
                            <h3>Acciones Rápidas</h3>
                            <a href="usuarios.php" class="btn">Crear Usuario</a>
                            <a href="propiedades.php" class="btn">Ver Propiedades</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
