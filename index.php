<?php
require_once 'config/database.php';
require_once 'includes/session.php';

$database = new Database();
$db = $database->getConnection();

function asset_exists(string $path): bool {
    if ($path === '') return false;
    $abs = __DIR__ . '/' . ltrim($path, '/');
    return is_file($abs);
}

$stmt = $db->prepare("SELECT * FROM configuracion_sitio LIMIT 1");
$stmt->execute();
$config = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$stmt = $db->prepare("SELECT id, titulo, descripcion_breve, precio, imagen_destacada 
                      FROM propiedades 
                      WHERE destacada = 1 
                      ORDER BY fecha_creacion DESC LIMIT 3");
$stmt->execute();
$destacadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT id, titulo, descripcion_breve, precio, imagen_destacada 
                      FROM propiedades 
                      WHERE tipo = 'venta' 
                      ORDER BY fecha_creacion DESC LIMIT 3");
$stmt->execute();
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare("SELECT id, titulo, descripcion_breve, precio, imagen_destacada 
                      FROM propiedades 
                      WHERE tipo = 'alquiler' 
                      ORDER BY fecha_creacion DESC LIMIT 3");
$stmt->execute();
$alquileres = $stmt->fetchAll(PDO::FETCH_ASSOC);

$busqueda = trim($_GET['buscar'] ?? '');
$resultados_busqueda = [];
if ($busqueda !== '') {
    $stmt = $db->prepare("SELECT id, titulo, descripcion_breve, descripcion_larga, precio, imagen_destacada 
                          FROM propiedades 
                          WHERE descripcion_breve LIKE :q OR descripcion_larga LIKE :q 
                          ORDER BY fecha_creacion DESC");
    $like = '%' . $busqueda . '%';
    $stmt->bindValue(':q', $like);
    $stmt->execute();
    $resultados_busqueda = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$hero_style = '';
if (!empty($config['imagen_banner']) && asset_exists($config['imagen_banner'])) {
    $url = htmlspecialchars($config['imagen_banner']);
    $hero_style = "style=\"background-image:url('{$url}');\"";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>UTN Solutions Real State</title>
  <link rel="stylesheet" href="css/styles.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;1,300&display=swap" rel="stylesheet">

</head>
<body class="tema-<?php echo htmlspecialchars($config['tema_color'] ?? 'azul'); ?>">
  <a href="login.php" class="login-icon"><i class="fas fa-user"></i></a>
  <header class="header">
    <div class="header-container">

      <div class="brand-block">
        <div class="logo">
          <?php if (!empty($config['logo_principal']) && asset_exists($config['logo_principal'])): ?>
            <img src="<?php echo htmlspecialchars($config['logo_principal']); ?>" alt="UTN Solutions Logo" class="logo-image">
          <?php else: ?>
            <div class="logo-icon"><i class="fas fa-building" style="font-size:30px;"></i></div>
          <?php endif; ?>
          <div class="logo-text">UTN SOLUTIONS<br>REAL STATE</div>
        </div>

        <div class="social-icons">
          <a href="<?php echo htmlspecialchars($config['facebook_url'] ?? '#'); ?>" class="social-icon facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="<?php echo htmlspecialchars($config['youtube_url'] ?? '#'); ?>" class="social-icon youtube"><i class="fab fa-youtube"></i></a>
          <a href="<?php echo htmlspecialchars($config['instagram_url'] ?? '#'); ?>" class="social-icon instagram"><i class="fab fa-instagram"></i></a>
        </div>
      </div>

      <div class="header-right">
        <nav>
          <ul class="nav-menu nav-with-pipes">
            <li><a href="index.php">INICIO</a></li>
            <li><a href="#quienes-somos">QUIENES SOMOS</a></li>
            <li><a href="alquileres.php">ALQUILERES</a></li>
            <li><a href="ventas.php">VENTAS</a></li>
            <li><a href="#contacto">CONTACTENOS</a></li>
          </ul>
        </nav>
        
        <form class="nav-search" method="GET" action="index.php">
          <input type="text" name="buscar" class="nav-search-input" placeholder="Buscar..." value="<?php echo htmlspecialchars($busqueda); ?>">
          <button type="submit" class="nav-search-btn"><i class="fas fa-search"></i></button>
        </form>
      </div>
    </div>
  </header>


        

  <?php if (!empty($resultados_busqueda)): ?>

    <section class="properties">
      <div class="properties-container">
        <h2>Resultados de búsqueda para: "<?php echo htmlspecialchars($busqueda); ?>"</h2>
        <div class="properties-grid">
          <?php foreach ($resultados_busqueda as $prop): ?>
            <div class="property-card">
              <?php if (!empty($prop['imagen_destacada']) && asset_exists($prop['imagen_destacada'])): ?>
                <img src="<?php echo htmlspecialchars($prop['imagen_destacada']); ?>" alt="<?php echo htmlspecialchars($prop['titulo']); ?>" class="property-image">
              <?php else: ?>
                <img src="/placeholder.svg?height=200&width=350" alt="<?php echo htmlspecialchars($prop['titulo']); ?>" class="property-image">
              <?php endif; ?>
              <div class="property-info">
                <h3 class="property-title"><?php echo htmlspecialchars($prop['titulo']); ?></h3>
                <p class="property-description"><?php echo htmlspecialchars($prop['descripcion_breve']); ?></p>
                <div class="property-price">Precio: ₡<?php echo number_format((float)$prop['precio']); ?></div>
                <a href="propiedad.php?id=<?php echo (int)$prop['id']; ?>" class="ver-mas-btn">Ver Detalles</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

  <?php else: ?>

    <section class="hero" <?php echo $hero_style; ?>>
      <div class="hero-container">
        <div class="hero-content">
          <h1><?php echo htmlspecialchars($config['mensaje_banner'] ?? 'PERMÍTENOS AYUDARTE A CUMPLIR TUS SUEÑOS'); ?></h1>
        </div>
      </div>
    </section>

    <section class="about" id="quienes-somos">
      <div class="about-container">
        <div class="about-content">
          <h2>QUIENES SOMOS</h2>
          <p><?php echo htmlspecialchars($config['quienes_somos_texto'] ?? 'Información sobre la empresa...'); ?></p>
        </div>
        <div class="about-image">
          <?php if (!empty($config['imagen_quienes_somos']) && asset_exists($config['imagen_quienes_somos'])): ?>
            <img src="<?php echo htmlspecialchars($config['imagen_quienes_somos']); ?>" alt="Quienes Somos">
          <?php else: ?>
            <img src="/placeholder.svg?height=200&width=200" alt="Equipo">
          <?php endif; ?>
        </div>
      </div>
    </section>

    <section class="properties dark">
      <div class="properties-container">
        <h2>PROPIEDADES DESTACADAS</h2>
        <div class="properties-grid">
          <?php foreach ($destacadas as $p): ?>
            <div class="property-card">
              <?php if (!empty($p['imagen_destacada']) && asset_exists($p['imagen_destacada'])): ?>
                <img src="<?php echo htmlspecialchars($p['imagen_destacada']); ?>" alt="<?php echo htmlspecialchars($p['titulo']); ?>" class="property-image">
              <?php else: ?>
                <img src="/placeholder.svg?height=200&width=350" alt="<?php echo htmlspecialchars($p['titulo']); ?>" class="property-image">
              <?php endif; ?>
              <div class="property-info">
                <h3 class="property-title"><?php echo htmlspecialchars($p['titulo']); ?></h3>
                <p class="property-description"><?php echo htmlspecialchars($p['descripcion_breve']); ?></p>
                <div class="property-price">Precio: ₡<?php echo number_format((float)$p['precio']); ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="ver-mas-container">
          <a href="destacadas.php" class="ver-mas-btn">VER MAS...</a>
        </div>
      </div>
    </section>

    <section class="properties">
      <div class="properties-container">
        <h2>PROPIEDADES EN VENTA</h2>
        <div class="properties-grid">
          <?php foreach ($ventas as $p): ?>
            <div class="property-card">
              <?php if (!empty($p['imagen_destacada']) && asset_exists($p['imagen_destacada'])): ?>
                <img src="<?php echo htmlspecialchars($p['imagen_destacada']); ?>" alt="<?php echo htmlspecialchars($p['titulo']); ?>" class="property-image">
              <?php else: ?>
                <img src="/placeholder.svg?height=200&width=350" alt="<?php echo htmlspecialchars($p['titulo']); ?>" class="property-image">
              <?php endif; ?>
              <div class="property-info">
                <h3 class="property-title"><?php echo htmlspecialchars($p['titulo']); ?></h3>
                <p class="property-description"><?php echo htmlspecialchars($p['descripcion_breve']); ?></p>
                <div class="property-price">Precio: ₡<?php echo number_format((float)$p['precio']); ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="ver-mas-container">
          <a href="ventas.php" class="ver-mas-btn">VER MAS...</a>
        </div>
      </div>
    </section>

    <section class="properties dark">
      <div class="properties-container">
        <h2>PROPIEDADES EN ALQUILER</h2>
        <div class="properties-grid">
          <?php foreach ($alquileres as $p): ?>
            <div class="property-card">
              <?php if (!empty($p['imagen_destacada']) && asset_exists($p['imagen_destacada'])): ?>
                <img src="<?php echo htmlspecialchars($p['imagen_destacada']); ?>" alt="<?php echo htmlspecialchars($p['titulo']); ?>" class="property-image">
              <?php else: ?>
                <img src="/placeholder.svg?height=200&width=350" alt="<?php echo htmlspecialchars($p['titulo']); ?>" class="property-image">
              <?php endif; ?>
              <div class="property-info">
                <h3 class="property-title"><?php echo htmlspecialchars($p['titulo']); ?></h3>
                <p class="property-description"><?php echo htmlspecialchars($p['descripcion_breve']); ?></p>
                <div class="property-price">Precio: ₡<?php echo number_format((float)$p['precio']); ?></div>
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

  <footer class="footer" id="contacto">
    <div class="footer-container">
      <div class="footer-section">
        <p><i class="fas fa-map-marker-alt"></i> <b>Dirección:</b> <?php echo htmlspecialchars($config['direccion'] ?? 'Cañas Guanacaste, 100 mts Este Parque de Cañas'); ?></p>
        <p><i class="fas fa-phone"></i> <b>Teléfono:</b> <?php echo htmlspecialchars($config['telefono_contacto'] ?? '8890-2030'); ?></p>
        <p><i class="fas fa-envelope"></i> <b>Email:</b> <?php echo htmlspecialchars($config['email_contacto'] ?? 'info@utnrealestate.com'); ?></p>
      </div>

    
      <div class="footer-section" style="text-align:center;">
        <?php if (!empty($config['logo_blanco']) && asset_exists($config['logo_blanco'])): ?>
          <img src="<?php echo htmlspecialchars($config['logo_blanco']); ?>" alt="UTN Solutions Logo" class="logo-image" style="max-width:80px;margin-bottom:10px;">
        <?php else: ?>
          <div class="logo-icon"><i class="fas fa-building" style="font-size:40px;"></i></div>
        <?php endif; ?>
        <div class="logo-text">UTN SOLUTIONS<br>REAL STATE</div>
        <div class="social-icons">
          <a href="<?php echo htmlspecialchars($config['facebook_url'] ?? '#'); ?>" class="social-icon facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="<?php echo htmlspecialchars($config['youtube_url'] ?? '#'); ?>" class="social-icon youtube"><i class="fab fa-youtube"></i></a>
          <a href="<?php echo htmlspecialchars($config['instagram_url'] ?? '#'); ?>" class="social-icon instagram"><i class="fab fa-instagram"></i></a>
        </div>
      </div>

     
      <div class="footer-section">
        <form class="contact-form" method="POST" action="enviar_mensaje.php">
          <div style="font-weight:bold;margin-bottom:10px;text-align:center;">Contáctanos</div>
          <div class="contact-form-row">
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" id="nombre" required>
          </div>
          <div class="contact-form-row">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
          </div>
          <div class="contact-form-row">
            <label for="telefono">Teléfono:</label>
            <input type="tel" name="telefono" id="telefono" required>
          </div>
          <div class="contact-form-row">
            <label for="mensaje">Mensaje:</label>
            <textarea name="mensaje" id="mensaje" rows="2" required></textarea>
          </div>
          <button type="submit">Enviar</button>
        </form>
      </div>
    </div>

    <div class="footer-bottom">
      <p>@ Derechos Reservados 2025 - UTN Solutions Real State</p>
    </div>
  </footer>
</body>
</html>
