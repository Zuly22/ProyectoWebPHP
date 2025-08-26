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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panel Agente - UTN Real Estate</title>
  <link rel="stylesheet" href="../css/styles.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
</head>
<body>
  <div class="admin-panel">
    <div class="admin-container">
      
      <div class="admin-header">
        <h1>Panel Agente</h1>
        <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></p>

        <div class="admin-nav">
          <a href="dashboard_agente.php" class="active"><i class="fas fa-home"></i> Inicio</a>
          <a href="propiedades.php"><i class="fas fa-building"></i> Mis Propiedades</a>
          <a href="perfil.php"><i class="fas fa-user"></i> Mi Perfil</a>
          <a href="../index.php"><i class="fas fa-globe"></i> Ver Sitio Web</a>
          <a href="../includes/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </div>
      </div>

      <div class="admin-content">
        <h2>Resumen Personal</h2>

        <div class="properties-grid">
          <div class="property-card">
            <div class="property-info">
              <h3>Total de Propiedades</h3>
              <div class="property-price"><?php echo (int)$stats['total']; ?></div>
            </div>
          </div>

          <div class="property-card">
            <div class="property-info">
              <h3>En Venta</h3>
              <div class="property-price"><?php echo (int)$stats['ventas']; ?></div>
            </div>
          </div>

          <div class="property-card">
            <div class="property-info">
              <h3>En Alquiler</h3>
              <div class="property-price"><?php echo (int)$stats['alquileres']; ?></div>
            </div>
          </div>

          <div class="property-card">
            <div class="property-info">
              <h3>Destacadas</h3>
              <div class="property-price"><?php echo (int)$stats['destacadas']; ?></div>
            </div>
          </div>
        </div>

        <div class="properties-grid" style="margin-top: 20px;">
          <div class="property-card">
            <div class="property-info">
              <h3>Acciones Rápidas</h3>
              <a href="propiedades.php" class="btn"><i class="fas fa-plus"></i> Nueva Propiedad</a>
              <a href="propiedades.php" class="btn"><i class="fas fa-list"></i> Ver Todas</a>
            </div>
          </div>
        </div>

        <!-- tabla igual al estilo admin -->
        <div class="card" style="margin-top:28px;">
          <h2 style="margin-top:0"><i class="fas fa-list"></i> Mis Propiedades</h2>
          <table class="table">
            <thead>
              <tr>
                <th>Imagen</th>
                <th>Título</th>
                <th>Tipo</th>
                <th>Precio</th>
                <th>Ubicación</th>
                <th>Destacada</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($propiedades as $p): ?>
              <tr>
                <td>
                  <?php
                    $img = $p['imagen'] ?? '';
                    $imgPath = !empty($img) ? '../'.$img : '';
                    if (!empty($imgPath) && file_exists($imgPath)):
                  ?>
                    <img class="img-thumb"
                         src="<?php echo htmlspecialchars($imgPath); ?>"
                         alt="<?php echo htmlspecialchars($p['titulo']); ?>">
                  <?php else: ?>
                    <span style="color:#9ca3af">—</span>
                  <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($p['titulo']); ?></td>
                <td><?php echo htmlspecialchars(ucfirst($p['tipo'])); ?></td>
                <td><?php echo '₡' . number_format((float)$p['precio'], 0, ',', '.'); ?></td>
                <td><?php echo htmlspecialchars($p['ubicacion'] ?? ''); ?></td>
                <td><?php echo !empty($p['destacada']) ? 'Sí' : 'No'; ?></td>
                <td>
                  <a class="btn warning" href="propiedades.php?edit=<?php echo (int)$p['id']; ?>">
                    <i class="fas fa-edit"></i> Editar
                  </a>
                  <form method="POST" style="display:inline"
                        onsubmit="return confirm('¿Estás seguro de eliminar esta propiedad?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                    <button type="submit" class="btn danger">
                      <i class="fas fa-trash"></i> Eliminar
                    </button>
                  </form>
                  <a class="btn secondary" target="_blank"
                     href="../propiedad.php?id=<?php echo (int)$p['id']; ?>">
                    <i class="fas fa-external-link-alt"></i> Ver
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($propiedades)): ?>
              <tr><td colspan="7" style="color:#6b7280">No tienes propiedades registradas.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>

    </div>
  </div>
</body>
</html>
