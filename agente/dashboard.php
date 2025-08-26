<?php
require_once '../config/database.php';
require_once '../includes/session.php';

if (!isset($_SESSION['user_id']) || $_SESSION['privilegio'] !== 'agente_ventas') {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT 
            id, titulo, tipo, precio, destacada,
            COALESCE(imagen_destacada, '') AS imagen
          FROM propiedades
          WHERE agente_id = :agente_id
          ORDER BY fecha_creacion DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':agente_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$propiedades = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query_stats = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN tipo = 'venta' THEN 1 ELSE 0 END) as ventas,
    SUM(CASE WHEN tipo = 'alquiler' THEN 1 ELSE 0 END) as alquileres,
    SUM(CASE WHEN destacada = 1 THEN 1 ELSE 0 END) as destacadas
    FROM propiedades WHERE agente_id = :agente_id";
$stmt_stats = $db->prepare($query_stats);
$stmt_stats->bindParam(':agente_id', $_SESSION['user_id'], PDO::PARAM_INT);
$stmt_stats->execute();
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Agente - UTN Real Estate</title>
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
                <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="propiedades.php"><i class="fas fa-building"></i> Mis Propiedades</a></li>
                <li><a href="perfil.php"><i class="fas fa-user"></i> Mi Perfil</a></li>
                <li><a href="../index.php"><i class="fas fa-globe"></i> Ver Sitio Web</a></li>
                <li><a href="../includes/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
            </ul>
        </nav>

        <main class="admin-content">
            <div class="admin-header">
                <h1>Dashboard del Agente</h1>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total']; ?></h3>
                        <p>Total Propiedades</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['ventas']; ?></h3>
                        <p>En Venta</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['alquileres']; ?></h3>
                        <p>En Alquiler</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['destacadas']; ?></h3>
                        <p>Destacadas</p>
                    </div>
                </div>
            </div>

            <div class="recent-properties">
                <h2>Mis Propiedades Recientes</h2>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Título</th>
                                <th>Tipo</th>
                                <th>Precio</th>
                                <th>Destacada</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($propiedades)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">No tienes propiedades registradas</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach (array_slice($propiedades, 0, 5) as $propiedad): ?>
                                    <tr>
                                        <td>
                                            <?php $img = $propiedad['imagen'] ?? ''; ?>
                                            <?php if (!empty($img)): ?>
                                                <img src="../<?= htmlspecialchars($img); ?>" 
                                                     alt="Propiedad" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                            <?php else: ?>
                                                <div style="width: 50px; height: 50px; background: #ddd; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($propiedad['titulo']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $propiedad['tipo'] === 'venta' ? 'badge-success' : 'badge-info'; ?>">
                                                <?php echo ucfirst($propiedad['tipo']); ?>
                                            </span>
                                        </td>
                                        <td>$<?php echo number_format($propiedad['precio']); ?></td>
                                        <td>
                                            <?php if ($propiedad['destacada']): ?>
                                                <span class="badge badge-warning">Destacada</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Normal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="propiedades.php?edit=<?php echo $propiedad['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (count($propiedades) > 5): ?>
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="propiedades.php" class="btn btn-primary">Ver Todas las Propiedades</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
