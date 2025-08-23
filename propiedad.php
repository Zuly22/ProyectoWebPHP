<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Obtener ID de la propiedad
$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Obtener datos de la propiedad
$query = "SELECT p.*, u.nombre as agente_nombre, u.telefono as agente_telefono, u.correo as agente_correo 
          FROM propiedades p 
          LEFT JOIN usuarios u ON p.agente_id = u.id 
          WHERE p.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id);
$stmt->execute();
$propiedad = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$propiedad) {
    header('Location: index.php');
    exit;
}

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
    <title><?php echo htmlspecialchars($propiedad['titulo']); ?> - UTN Solutions Real State</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-building" style="font-size: 30px;"></i>
                </div>
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
                <a href="login.php" class="login-icon">
                    <i class="fas fa-user"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Detalle de Propiedad -->
    <section class="properties">
        <div class="properties-container">
            <div class="property-detail">
                <h1><?php echo htmlspecialchars($propiedad['titulo']); ?></h1>
                
                <div class="property-detail-grid">
                    <div class="property-detail-image">
                        <img src="/placeholder.svg?height=400&width=600" alt="<?php echo $propiedad['titulo']; ?>">
                    </div>
                    
                    <div class="property-detail-info">
                        <div class="property-price" style="font-size: 2rem; margin-bottom: 20px;">
                            $<?php echo number_format($propiedad['precio']); ?>
                        </div>
                        
                        <div class="property-type">
                            <strong>Tipo:</strong> <?php echo ucfirst($propiedad['tipo']); ?>
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
                
                <div class="property-actions">
                    <a href="<?php echo $propiedad['tipo'] == 'venta' ? 'ventas.php' : 'alquileres.php'; ?>" class="btn">
                        Volver a <?php echo ucfirst($propiedad['tipo']); ?>s
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <div class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-building" style="font-size: 30px; color: #1a1a2e;"></i>
                    </div>
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

