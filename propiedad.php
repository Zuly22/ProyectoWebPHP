<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = "SELECT p.*, u.nombre as agente_nombre, u.telefono as agente_telefono, u.correo as agente_correo 
          FROM propiedades p 
          LEFT JOIN usuarios u ON p.agente_id = u.id 
          WHERE p.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$propiedad = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$propiedad) {
    header('Location: index.php');
    exit;
}

$query = "SELECT * FROM configuracion_sitio LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute();
$config = $stmt->fetch(PDO::FETCH_ASSOC);

$img = (!empty($propiedad['imagen_destacada']) && file_exists($propiedad['imagen_destacada']))
    ? $propiedad['imagen_destacada']
    : '/placeholder.svg?height=400&width=600';

$mapsUrl = '';
if (!empty($propiedad['mapa'])) {
    $addr = urlencode($propiedad['mapa']);
    $mapsUrl = "https://www.google.com/maps/search/?api=1&query={$addr}";
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($propiedad['titulo']); ?> - UTN Solutions Real State</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="tema-<?php echo htmlspecialchars($config['tema_color']); ?>">
    <a href="login.php" class="login-icon">
        <i class="fas fa-user"></i>
    </a>
    <header class="header">
        <div class="header-container">
            <div class="logo">
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
        </div>

        <div class="header-right">
             <nav>
          <ul class="nav-menu nav-with-pipes">
            <li><a href="index.php">INICIO</a></li>
            <li><a href="index.php#quienes-somos">QUIENES SOMOS</a></li>
            <li><a href="alquileres.php">ALQUILERES</a></li>
            <li><a href="ventas.php">VENTAS</a></li>
            <li><a href="index.php#contacto">CONTACTENOS</a></li>
          </ul>
        </nav>
        </div>
    </div>
</header>

    <section class="properties">
        <div class="properties-container">
            <div class="property-detail">
                <h1><?php echo htmlspecialchars($propiedad['titulo']); ?></h1>
                
                <div class="property-detail-grid">
                    <div class="property-detail-image">
                        <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($propiedad['titulo']); ?>">
                    </div>
                    
                    <div class="property-detail-info">
                        <div class="property-price" style="font-size: 2rem; margin-bottom: 20px;">
                            ₡<?php echo number_format((float)$propiedad['precio']); ?>
                        </div>
                        
                        <div class="property-type">
                            <strong>Tipo:</strong> <?php echo htmlspecialchars(ucfirst($propiedad['tipo'])); ?>
                        </div>
                        
                        <div class="property-location">
                            <strong>Ubicación:</strong> <?php echo htmlspecialchars($propiedad['ubicacion']); ?>
                        </div>
                        
                        <div class="property-agent">
                            <h3>Agente de Ventas</h3>
                            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($propiedad['agente_nombre']); ?></p>
                            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($propiedad['agente_telefono']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($propiedad['agente_correo']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="property-description">
                    <h3>Descripción</h3>
                    <p><?php echo nl2br(htmlspecialchars($propiedad['descripcion_larga'] ?: $propiedad['descripcion_breve'])); ?></p>
                </div>

                <?php if ($mapsUrl): ?>
                <div class="property-map" style="margin:30px 0;">
                    <h3>Ubicación</h3>
                    <p>
                        <a href="<?php echo $mapsUrl; ?>" target="_blank" class="btn">
                            Ver en Google Maps
                        </a>
                    </p>
                </div>
                <?php endif; ?>

                <div class="property-actions">
                    <a href="<?php echo ($propiedad['tipo'] === 'venta') ? 'ventas.php' : 'alquileres.php'; ?>" class="btn">
                        Volver a <?php echo htmlspecialchars(ucfirst($propiedad['tipo'])); ?>s
                    </a>
                </div>
            </div>
        </div>
    </section>

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

    <style>
    .property-detail { 
        max-width: 1000px; 
        margin: 0 auto; 
        padding: 40px 20px; 
    }
    .property-detail h1 { 
        text-align: center; 
        margin-bottom: 30px; 
        color: #1a1a2e; 
    }
    .property-detail-grid { 
        display: grid; 
        grid-template-columns: 2fr 1fr; 
        gap: 40px; 
        margin-bottom: 40px; 
    }
    .property-detail-image img { 
        width: 100%; 
        height: 400px; 
        object-fit: cover; 
        border-radius: 10px; 
    }
    .property-detail-info { 
        background: #f8f9fa; 
        padding: 30px; 
        border-radius: 10px; 
    }
    .property-detail-info > div { 
        margin-bottom: 20px; 
    }
    .property-agent { 
        background: white; 
        padding: 20px; 
        border-radius: 10px; 
        margin-top: 20px; 
    }
    .property-description { 
        background: #f8f9fa; 
        padding: 30px; 
        border-radius: 10px; 
        margin-bottom: 30px; 
    }
    .property-actions { 
        text-align: center; 
    }
    @media (max-width: 768px) {
        .property-detail-grid {
            grid-template-columns: 1fr; 
        }
    }
    </style>
</body>
</html>


