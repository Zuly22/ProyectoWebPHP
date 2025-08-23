<?php
require_once 'config/database.php';
require_once 'includes/session.php';

$database = new Database();
$db = $database->getConnection();

// Obtener configuración del sitio
$query = "SELECT * FROM configuracion_sitio LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute();
$config = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener propiedades destacadas (últimas 3)
$query = "SELECT * FROM propiedades WHERE destacada = 1 ORDER BY fecha_creacion DESC LIMIT 3";
$stmt = $db->prepare($query);
$stmt->execute();
$destacadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener propiedades en venta (últimas 3)
$query = "SELECT * FROM propiedades WHERE tipo = 'venta' ORDER BY fecha_creacion DESC LIMIT 3";
$stmt = $db->prepare($query);
$stmt->execute();
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener propiedades en alquiler (últimas 3)
$query = "SELECT * FROM propiedades WHERE tipo = 'alquiler' ORDER BY fecha_creacion DESC LIMIT 3";
$stmt = $db->prepare($query);
$stmt->execute();
$alquileres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar búsqueda
$busqueda = '';
$resultados_busqueda = [];
if (isset($_GET['buscar']) && !empty($_GET['buscar'])) {
    $busqueda = $_GET['buscar'];
    $query = "SELECT * FROM propiedades WHERE descripcion_breve LIKE :busqueda OR descripcion_larga LIKE :busqueda";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':busqueda', '%' . $busqueda . '%');
    $stmt->execute();
    $resultados_busqueda = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UTN Solutions Real State</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="tema-<?php echo htmlspecialchars($config['tema_color']); ?>">

    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <div class="logo">
                <!-- Using dynamic logo from database configuration -->
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
                    <li><a href="#quienes-somos">QUIENES SOMOS</a></li>
                    <li><a href="alquileres.php">ALQUILERES</a></li>
                    <li><a href="ventas.php">VENTAS</a></li>
                    <li><a href="#contacto">CONTACTENOS</a></li>
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
                
                <form class="search-container" method="GET">
                    <input type="text" name="buscar" class="search-input" placeholder="Buscar propiedades..." value="<?php echo htmlspecialchars($busqueda); ?>">
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

    <?php if (!empty($resultados_busqueda)): ?>
    <!-- Resultados de búsqueda -->
    <section class="properties">
        <div class="properties-container">
            <h2>Resultados de búsqueda para: "<?php echo htmlspecialchars($busqueda); ?>"</h2>
            <div class="properties-grid">
                <?php foreach ($resultados_busqueda as $propiedad): ?>
                <div class="property-card">
                    <!-- Using dynamic property images with fallback -->
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
    <?php else: ?>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1><?php echo $config['mensaje_banner'] ?? 'PERMITENOS SAYUDARTE A CUMPLIR TUS SUEÑOS'; ?></h1>
                <!-- Using dynamic banner image from database -->
                <?php if (!empty($config['imagen_banner']) && file_exists($config['imagen_banner'])): ?>
                    <img src="<?php echo $config['imagen_banner']; ?>" alt="Banner Principal" class="hero-image">
                <?php else: ?>
                    <img src="/placeholder.svg?height=300&width=500" alt="Casa 3D" class="hero-image">
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="quienes-somos">
        <div class="about-container">
            <div class="about-content">
                <h2>QUIENES SOMOS</h2>
                <p><?php echo $config['quienes_somos_texto'] ?? 'Información sobre la empresa...'; ?></p>
            </div>
            <div class="about-image">
                <!-- Using dynamic about section image -->
                <?php if (!empty($config['imagen_quienes_somos']) && file_exists($config['imagen_quienes_somos'])): ?>
                    <img src="<?php echo $config['imagen_quienes_somos']; ?>" alt="Quienes Somos">
                <?php else: ?>
                    <img src="/placeholder.svg?height=200&width=200" alt="Equipo">
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Propiedades Destacadas -->
    <section class="properties dark">
        <div class="properties-container">
            <h2>PROPIEDADES DESTACADAS</h2>
            <div class="properties-grid">
                <?php foreach ($destacadas as $propiedad): ?>
                <div class="property-card">
                    <!-- Using dynamic property images with fallback -->
                    <?php if (!empty($propiedad['imagen_destacada']) && file_exists($propiedad['imagen_destacada'])): ?>
                        <img src="<?php echo $propiedad['imagen_destacada']; ?>" alt="<?php echo $propiedad['titulo']; ?>" class="property-image">
                    <?php else: ?>
                        <img src="/placeholder.svg?height=200&width=350" alt="<?php echo $propiedad['titulo']; ?>" class="property-image">
                    <?php endif; ?>
                    <div class="property-info">
                        <h3 class="property-title"><?php echo $propiedad['titulo']; ?></h3>
                        <p class="property-description"><?php echo $propiedad['descripcion_breve']; ?></p>
                        <div class="property-price">Precio: $<?php echo number_format($propiedad['precio']); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="ver-mas-container">
                <a href="destacadas.php" class="ver-mas-btn">VER MAS...</a>
            </div>
        </div>
    </section>

    <!-- Propiedades en Venta -->
    <section class="properties">
        <div class="properties-container">
            <h2>PROPIEDADES EN VENTA</h2>
            <div class="properties-grid">
                <?php foreach ($ventas as $propiedad): ?>
                <div class="property-card">
                    <?php if (!empty($propiedad['imagen_destacada']) && file_exists($propiedad['imagen_destacada'])): ?>
                        <img src="<?php echo $propiedad['imagen_destacada']; ?>" alt="<?php echo $propiedad['titulo']; ?>" class="property-image">
                    <?php else: ?>
                        <img src="/placeholder.svg?height=200&width=350" alt="<?php echo $propiedad['titulo']; ?>" class="property-image">
                    <?php endif; ?>
                    <div class="property-info">
                        <h3 class="property-title"><?php echo $propiedad['titulo']; ?></h3>
                        <p class="property-description"><?php echo $propiedad['descripcion_breve']; ?></p>
                        <div class="property-price">Precio: $<?php echo number_format($propiedad['precio']); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="ver-mas-container">
                <a href="ventas.php" class="ver-mas-btn">VER MAS...</a>
            </div>
        </div>
    </section>

    <!-- Propiedades en Alquiler -->
    <section class="properties dark">
        <div class="properties-container">
            <h2>PROPIEDADES EN ALQUILER</h2>
            <div class="properties-grid">
                <?php foreach ($alquileres as $propiedad): ?>
                <div class="property-card">
                    <?php if (!empty($propiedad['imagen_destacada']) && file_exists($propiedad['imagen_destacada'])): ?>
                        <img src="<?php echo $propiedad['imagen_destacada']; ?>" alt="<?php echo $propiedad['titulo']; ?>" class="property-image">
                    <?php else: ?>
                        <img src="/placeholder.svg?height=200&width=350" alt="<?php echo $propiedad['titulo']; ?>" class="property-image">
                    <?php endif; ?>
                    <div class="property-info">
                        <h3 class="property-title"><?php echo $propiedad['titulo']; ?></h3>
                        <p class="property-description"><?php echo $propiedad['descripcion_breve']; ?></p>
                        <div class="property-price">Precio: $<?php echo number_format($propiedad['precio']); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="ver-mas-container">
                <a href="alquileres.php" class="ver-mas-btn">VER MAS...</a>
            </div>
        </div>
    </section>

    <?php endif; ?>

    <!-- Footer -->
    <footer class="footer" id="contacto">
        <div class="footer-container">
            <div class="footer-section">
                <div class="logo">
                    <!-- Using dynamic white logo in footer -->
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
                <div class="social-icons" style="margin-top: 20px;">
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
            </div>
            
            <div class="footer-section">
                <h3>Contactenos</h3>
                <p><strong>Nombre:</strong></p>
                <p><strong>Email:</strong></p>
                <p><strong>Teléfono:</strong></p>
                <p><strong>Mensaje:</strong></p>
                <form class="contact-form" method="POST" action="enviar_mensaje.php">
                    <input type="text" name="nombre" placeholder="Nombre" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="tel" name="telefono" placeholder="Teléfono" required>
                    <textarea name="mensaje" placeholder="Mensaje" rows="4" required></textarea>
                    <button type="submit">Enviar</button>
                </form>
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

