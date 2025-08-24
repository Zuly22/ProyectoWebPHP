<?php
require_once 'config/database.php';
require_once 'includes/session.php';

$database = new Database();
$db = $database->getConnection();

// Configuración del sitio
$query = "SELECT * FROM configuracion_sitio LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute();
$config = $stmt->fetch(PDO::FETCH_ASSOC);

// Destacadas (3)
$query = "SELECT * FROM propiedades WHERE destacada = 1 ORDER BY fecha_creacion DESC LIMIT 3";
$stmt = $db->prepare($query);
$stmt->execute();
$destacadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ventas (3)
$query = "SELECT * FROM propiedades WHERE tipo = 'venta' ORDER BY fecha_creacion DESC LIMIT 3";
$stmt = $db->prepare($query);
$stmt->execute();
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Alquileres (3)
$query = "SELECT * FROM propiedades WHERE tipo = 'alquiler' ORDER BY fecha_creacion DESC LIMIT 3";
$stmt = $db->prepare($query);
$stmt->execute();
$alquileres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Búsqueda
$busqueda = '';
$resultados_busqueda = [];
if (isset($_GET['buscar']) && $_GET['buscar'] !== '') {
    $busqueda = trim($_GET['buscar']);
    $query = "SELECT * FROM propiedades 
              WHERE descripcion_breve LIKE :busqueda OR descripcion_larga LIKE :busqueda
              ORDER BY fecha_creacion DESC";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':busqueda', '%'.$busqueda.'%');
    $stmt->execute();
    $resultados_busqueda = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Helper de imagen con fallback
function img_prop_or_placeholder(array $p, int $h = 200, int $w = 350): string {
    $path = $p['imagen_destacada'] ?? '';
    if (!empty($path) && file_exists($path)) {
        return htmlspecialchars($path);
    }
    return "/placeholder.svg?height={$h}&width={$w}";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inmobiliaria Pro</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="tema-<?php echo htmlspecialchars($config['tema_color']); ?>">

    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <div class="logo">
                <?php if (!empty($config['logo_principal']) && file_exists($config['logo_principal'])): ?>
                    <img src="<?php echo htmlspecialchars($config['logo_principal']); ?>" alt="UTN Solutions Logo" class="logo-image">
                <?php else: ?>
                    <div class="logo-icon"><i class="fas fa-building" style="font-size: 30px;"></i></div>
                <?php endif; ?>
                <div class="logo-text">
                    UTN SOLUTIONS<br>REAL STATE
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
                    <a href="<?php echo htmlspecialchars($config['facebook_url'] ?? '#'); ?>" class="social-icon facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="<?php echo htmlspecialchars($config['youtube_url'] ?? '#'); ?>" class="social-icon youtube"><i class="fab fa-youtube"></i></a>
                    <a href="<?php echo htmlspecialchars($config['instagram_url'] ?? '#'); ?>" class="social-icon instagram"><i class="fab fa-instagram"></i></a>
                </div>

                <form class="search-container" method="GET">
                    <input type="text" name="buscar" class="search-input" placeholder="Buscar propiedades..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                </form>

                <a href="login.php" class="login-icon"><i class="fas fa-user"></i></a>
            </div>
        </div>
    </header>

    <?php if (!empty($resultados_busqueda)): ?>
    <!-- Resultados de búsqueda -->
    <section class="properties">
        <div class="properties-container">
            <h2>Resultados de búsqueda para: "<?php echo htmlspecialchars($busqueda); ?>"</h2>
            <div class="properties-grid">
                <?php foreach ($resultados_busqueda as $p): ?>
                    <a class="property-card" href="propiedad.php?id=<?php echo (int)$p['id']; ?>">
                        <img src="<?php echo img_prop_or_placeholder($p); ?>" alt="<?php echo htmlspecialchars($p['titulo']); ?>" class="property-image">
                        <div class="property-info">
                            <h3 class="property-title"><?php echo htmlspecialchars($p['titulo']); ?></h3>
                            <p class="property-description"><?php echo htmlspecialchars($p['descripcion_breve']); ?></p>
                            <div class="property-price">₡<?php echo number_format((float)$p['precio'], 0, ',', '.'); ?></div>
                            <span class="ver-mas-btn">Ver Detalles</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php else: ?>
    <!-- Hero -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1><?php echo htmlspecialchars($config['mensaje_banner'] ?? 'PERMÍTENOS AYUDARTE A CUMPLIR TUS SUEÑOS'); ?></h1>
                <?php if (!empty($config['imagen_banner']) && file_exists($config['imagen_banner'])): ?>
                    <img src="<?php echo htmlspecialchars($config['imagen_banner']); ?>" alt="Banner Principal" class="hero-image">
                <?php else: ?>
                    <img src="/placeholder.svg?height=300&width=500" alt="Casa 3D" class="hero-image">
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- About -->
    <section class="about" id="quienes-somos">
        <div class="about-container">
            <div class="about-content">
                <h2>QUIENES SOMOS</h2>
                <p><?php echo htmlspecialchars($config['quienes_somos_texto'] ?? 'Información sobre la empresa...'); ?></p>
            </div>
            <div class="about-image">
                <?php if (!empty($config['imagen_quienes_somos']) && file_exists($config['imagen_quienes_somos'])): ?>
                    <img src="<?php echo htmlspecialchars($config['imagen_quienes_somos']); ?>" alt="Quienes Somos">
                <?php else: ?>
                    <img src="/placeholder.svg?height=200&width=200" alt="Equipo">
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Destacadas -->
    <section class="properties dark">
        <div class="properties-container">
            <h2>PROPIEDADES DESTACADAS</h2>
            <div class="properties-grid">
                <?php foreach ($destacadas as $p): ?>
                    <a class="property-card" href="propiedad.php?id=<?php echo (int)$p['id']; ?>">
                        <img src="<?php echo img_prop_or_placeholder($p); ?>" alt="<?php echo htmlspecialchars($p['titulo']); ?>" class="property-image">
                        <div class="property-info">
                            <h3 class="property-title"><?php echo htmlspecialchars($p['titulo']); ?></h3>
                            <p class="property-description"><?php echo htmlspecialchars($p['descripcion_breve']); ?></p>
                            <div class="property-price">₡<?php echo number_format((float)$p['precio'], 0, ',', '.'); ?></div>
                            <span class="ver-mas-btn">Ver Detalles</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="ver-mas-container">
                <a href="destacadas.php" class="ver-mas-btn">VER MÁS...</a>
            </div>
        </div>
    </section>

    <!-- Ventas -->
    <section class="properties">
        <div class="properties-container">
            <h2>PROPIEDADES EN VENTA</h2>
            <div class="properties-grid">
                <?php foreach ($ventas as $p): ?>
                    <a class="property-card" href="propiedad.php?id=<?php echo (int)$p['id']; ?>">
                        <img src="<?php echo img_prop_or_placeholder($p); ?>" alt="<?php echo htmlspecialchars($p['titulo']); ?>" class="property-image">
                        <div class="property-info">
                            <h3 class="property-title"><?php echo htmlspecialchars($p['titulo']); ?></h3>
                            <p class="property-description"><?php echo htmlspecialchars($p['descripcion_breve']); ?></p>
                            <div class="property-price">₡<?php echo number_format((float)$p['precio'], 0, ',', '.'); ?></div>
                            <span class="ver-mas-btn">Ver Detalles</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="ver-mas-container">
                <a href="ventas.php" class="ver-mas-btn">VER MÁS...</a>
            </div>
        </div>
    </section>

    <!-- Alquileres -->
    <section class="properties dark">
        <div class="properties-container">
            <h2>PROPIEDADES EN ALQUILER</h2>
            <div class="properties-grid">
                <?php foreach ($alquileres as $p): ?>
                    <a class="property-card" href="propiedad.php?id=<?php echo (int)$p['id']; ?>">
                        <img src="<?php echo img_prop_or_placeholder($p); ?>" alt="<?php echo htmlspecialchars($p['titulo']); ?>" class="property-image">
                        <div class="property-info">
                            <h3 class="property-title"><?php echo htmlspecialchars($p['titulo']); ?></h3>
                            <p class="property-description"><?php echo htmlspecialchars($p['descripcion_breve']); ?></p>
                            <div class="property-price">₡<?php echo number_format((float)$p['precio'], 0, ',', '.'); ?></div>
                            <span class="ver-mas-btn">Ver Detalles</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="ver-mas-container">
                <a href="alquileres.php" class="ver-mas-btn">VER MÁS...</a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="footer" id="contacto">
        <div class="footer-container">
            <div class="footer-section">
                <?php if (!empty($config['logo_blanco']) && file_exists($config['logo_blanco'])): ?>
                    <img src="<?php echo htmlspecialchars($config['logo_blanco']); ?>" alt="UTN Solutions Logo" class="logo-image" style="filter: brightness(0);">
                <?php else: ?>
                    <div class="logo-icon"><i class="fas fa-building" style="font-size: 30px; color: #1a1a2e;"></i></div>
                <?php endif; ?>
                <div class="logo-text" style="color: #1a1a2e;">
                    UTN SOLUTIONS<br>REAL STATE
                </div>
                <div class="social-icons" style="margin-top: 20px;">
                    <a href="<?php echo htmlspecialchars($config['facebook_url'] ?? '#'); ?>" class="social-icon facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="<?php echo htmlspecialchars($config['youtube_url'] ?? '#'); ?>" class="social-icon youtube"><i class="fab fa-youtube"></i></a>
                    <a href="<?php echo htmlspecialchars($config['instagram_url'] ?? '#'); ?>" class="social-icon instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>

            <div class="footer-section">
                <h3>Contáctenos</h3>
                <form class="contact-form" method="POST" action="enviar_mensaje.php">
                    <input type="text" name="nombre" placeholder="Nombre" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="tel" name="telefono" placeholder="Teléfono" required>
                    <textarea name="mensaje" placeholder="Mensaje" rows="4" required></textarea>
                    <button type="submit">Enviar</button>
                </form>
            </div>

            <div class="footer-section">
                <p><i class="fas fa-map-marker-alt"></i> Dirección: <?php echo htmlspecialchars($config['direccion'] ?? 'Cañas Guanacaste, 100 mts Este'); ?></p>
                <p><i class="fas fa-phone"></i> Teléfono: <?php echo htmlspecialchars($config['telefono_contacto'] ?? '8800-3030'); ?></p>
                <p><i class="fas fa-envelope"></i> Email: <?php echo htmlspecialchars($config['email_contacto'] ?? 'info@utnrealestate.com'); ?></p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>Derechos Reservados 2025</p>
        </div>
    </footer>
</body>
</html>
