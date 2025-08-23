<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Obtener todas las propiedades en alquiler
$query = "SELECT * FROM propiedades WHERE tipo = 'alquiler' ORDER BY fecha_creacion DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$propiedades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener configuración del sitio
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
    <title>Propiedades en Alquiler - UTN Solutions Real State</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <div class="logo">
                <!-- Adding dynamic logo support like in other pages -->
                <?php if (!empty($config['logo_principal']) && file_exists($config['logo_principal'])): ?>
                    <img src="<?php echo $config['logo_principal']; ?>" alt="UTN Solutions Logo" class="logo-image">
                <?php else: ?>
                    <div class="logo-icon">
                        <i class="fas fa-building" style="font-size: 30px;"></i>
                    </div>
                <?php endif; ?>
                <div class="logo-text">
                    UTN SOLUTIONS<br>
                    REAL STATE
                </div>
            </div>
            
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php">INICIO</a></li>
                    <li><a href="index.php#quienes-somos">QUIENES SOMOS</a></li>
                    <li><a href="alquileres.php">ALQUILERES</a></li>
                    <li><a href="ventas.php">VENTAS</a></li>
                    <li><a href="index.php#contacto">CONTACTENOS</a></li>
                </ul>
            </nav>
            
            <div class="header-right">
                <div class="social-icons">
                    <a href="<?php echo $config['facebook_url'] ?? '#'; ?>" class="social-icon facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="<?php echo $config['youtube_url'] ?? '#'; ?>" class="social-icon youtube">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="<?php echo $config['instagram_url'] ?? '#'; ?>" class="social-icon instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
                
                <form class="search-container" method="GET" action="index.php">
                    <input type="text" name="buscar" class="search-input" placeholder="Buscar propiedades...">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                
                <a href="login.php" class="login-icon">
                    <i class="fas fa-user"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Propiedades en Alquiler -->
    <section class="properties dark">
        <div class="properties-container">
            <h2>PROPIEDADES EN ALQUILER</h2>
            <div class="properties-grid">
                <?php foreach ($propiedades as $propiedad): ?>
                <div class="property-card">
                    <!-- Adding dynamic image support for rental properties -->
                    <?php if (!empty($propiedad['imagen_destacada']) && file_exists($propiedad['imagen_destacada'])): ?>
                        <img src="<?php echo $propiedad['imagen_destacada']; ?>" alt="<?php echo $propiedad['titulo']; ?>" class="property-image">
                    <?php else: ?>
                        <img src="/placeholder.svg?height=200&width=350" alt="<?php echo $propiedad['titulo']; ?>" class="property-image">
                    <?php endif; ?>
                    <div class="property-info">
                        <h3 class="property-title"><?php echo $propiedad['titulo']; ?></h3>
                        <p class="property-description"><?php echo $propiedad['descripcion_breve']; ?></p>
                        <div class="property-price">Precio: $<?php echo number_format($propiedad['precio']); ?></div>
                        <a href="propiedad.php?id=<?php echo $propiedad['id']; ?>" class="ver-mas-btn">Ver Detalles</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <div class="logo">
                    <!-- Adding dynamic white logo support in footer -->
                    <?php if (!empty($config['logo_blanco']) && file_exists($config['logo_blanco'])): ?>
                        <img src="<?php echo $config['logo_blanco']; ?>" alt="UTN Solutions Logo" class="logo-image" style="filter: brightness(0);">
                    <?php else: ?>
                        <div class="logo-icon">
                            <i class="fas fa-building" style="font-size: 30px; color: #1a1a2e;"></i>
                        </div>
                    <?php endif; ?>
                    <div class="logo-text" style="color: #1a1a2e;">
                        UTN SOLUTIONS<br>
                        REAL STATE
                    </div>
                </div>
            </div>
            
            <div class="footer-section">
                <p><i class="fas fa-map-marker-alt"></i> Dirección: <?php echo $config['direccion'] ?? 'Cañas Guanacaste, 100 mts Este'; ?></p>
                <p><i class="fas fa-phone"></i> Teléfono: <?php echo $config['telefono_contacto'] ?? '8800-3030'; ?></p>
                <p><i class="fas fa-envelope"></i> Email: <?php echo $config['email_contacto'] ?? 'info@utnrealestate.com'; ?></p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>Derechos Reservados 2024</p>
        </div>
    </footer>
</body>
</html>

