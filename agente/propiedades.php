<?php
require_once '../config/database.php';
require_once '../includes/session.php';

// Verificar que el usuario esté logueado y sea agente de ventas
if (!isset($_SESSION['user_id']) || $_SESSION['privilegio'] !== 'agente_ventas') {
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Agregar nueva propiedad
                $titulo = $_POST['titulo'] ?? '';
                $descripcion = $_POST['descripcion'] ?? '';
                $precio = $_POST['precio'] ?? '';
                $tipo = $_POST['tipo'] ?? '';
                $destacada = isset($_POST['destacada']) ? 1 : 0;
                $descripcion_larga = $_POST['descripcion_larga'] ?? '';
                $ubicacion = $_POST['ubicacion'] ?? '';
                $mapa = $_POST['mapa'] ?? '';
                
                // Manejar subida de imagen
                $imagen = '';
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                    $upload_dir = '../uploads/propiedades/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                    $filename = uniqid() . '.' . $file_extension;
                    $filepath = $upload_dir . $filename;
                    
                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $filepath)) {
                        $imagen = 'uploads/propiedades/' . $filename;
                    }
                }
                
                $query = "INSERT INTO propiedades (titulo, descripcion, precio, tipo, destacada, agente_id, imagen, descripcion_larga, ubicacion, mapa) 
                         VALUES (:titulo, :descripcion, :precio, :tipo, :destacada, :agente_id, :imagen, :descripcion_larga, :ubicacion, :mapa)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':titulo', $titulo);
                $stmt->bindParam(':descripcion', $descripcion);
                $stmt->bindParam(':precio', $precio);
                $stmt->bindParam(':tipo', $tipo);
                $stmt->bindParam(':destacada', $destacada);
                $stmt->bindParam(':agente_id', $_SESSION['user_id']);
                $stmt->bindParam(':imagen', $imagen);
                $stmt->bindParam(':descripcion_larga', $descripcion_larga);
                $stmt->bindParam(':ubicacion', $ubicacion);
                $stmt->bindParam(':mapa', $mapa);
                
                if ($stmt->execute()) {
                    $message = 'Propiedad agregada exitosamente';
                } else {
                    $error = 'Error al agregar la propiedad';
                }
                break;
                
            case 'edit':
                // Editar propiedad existente
                $id = $_POST['id'] ?? '';
                $titulo = $_POST['titulo'] ?? '';
                $descripcion = $_POST['descripcion'] ?? '';
                $precio = $_POST['precio'] ?? '';
                $tipo = $_POST['tipo'] ?? '';
                $destacada = isset($_POST['destacada']) ? 1 : 0;
                $descripcion_larga = $_POST['descripcion_larga'] ?? '';
                $ubicacion = $_POST['ubicacion'] ?? '';
                $mapa = $_POST['mapa'] ?? '';
                
                // Verificar que la propiedad pertenece al agente
                $check_query = "SELECT imagen FROM propiedades WHERE id = :id AND agente_id = :agente_id";
                $check_stmt = $db->prepare($check_query);
                $check_stmt->bindParam(':id', $id);
                $check_stmt->bindParam(':agente_id', $_SESSION['user_id']);
                $check_stmt->execute();
                
                if ($check_stmt->rowCount() > 0) {
                    $current_property = $check_stmt->fetch(PDO::FETCH_ASSOC);
                    $imagen = $current_property['imagen'];
                    
                    // Manejar nueva imagen si se subió
                    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                        $upload_dir = '../uploads/propiedades/';
                        if (!file_exists($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        $file_extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                        $filename = uniqid() . '.' . $file_extension;
                        $filepath = $upload_dir . $filename;
                        
                        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $filepath)) {
                            // Eliminar imagen anterior si existe
                            if ($imagen && file_exists('../' . $imagen)) {
                                unlink('../' . $imagen);
                            }
                            $imagen = 'uploads/propiedades/' . $filename;
                        }
                    }
                    
                    $query = "UPDATE propiedades SET titulo = :titulo, descripcion = :descripcion, precio = :precio, 
                             tipo = :tipo, destacada = :destacada, imagen = :imagen, descripcion_larga = :descripcion_larga,
                             ubicacion = :ubicacion, mapa = :mapa WHERE id = :id AND agente_id = :agente_id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':titulo', $titulo);
                    $stmt->bindParam(':descripcion', $descripcion);
                    $stmt->bindParam(':precio', $precio);
                    $stmt->bindParam(':tipo', $tipo);
                    $stmt->bindParam(':destacada', $destacada);
                    $stmt->bindParam(':imagen', $imagen);
                    $stmt->bindParam(':descripcion_larga', $descripcion_larga);
                    $stmt->bindParam(':ubicacion', $ubicacion);
                    $stmt->bindParam(':mapa', $mapa);
                    $stmt->bindParam(':id', $id);
                    $stmt->bindParam(':agente_id', $_SESSION['user_id']);
                    
                    if ($stmt->execute()) {
                        $message = 'Propiedad actualizada exitosamente';
                    } else {
                        $error = 'Error al actualizar la propiedad';
                    }
                } else {
                    $error = 'No tienes permisos para editar esta propiedad';
                }
                break;
                
            case 'delete':
                $id = $_POST['id'] ?? '';
                
                // Verificar que la propiedad pertenece al agente y obtener imagen
                $check_query = "SELECT imagen FROM propiedades WHERE id = :id AND agente_id = :agente_id";
                $check_stmt = $db->prepare($check_query);
                $check_stmt->bindParam(':id', $id);
                $check_stmt->bindParam(':agente_id', $_SESSION['user_id']);
                $check_stmt->execute();
                
                if ($check_stmt->rowCount() > 0) {
                    $property = $check_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $query = "DELETE FROM propiedades WHERE id = :id AND agente_id = :agente_id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':id', $id);
                    $stmt->bindParam(':agente_id', $_SESSION['user_id']);
                    
                    if ($stmt->execute()) {
                        // Eliminar imagen si existe
                        if ($property['imagen'] && file_exists('../' . $property['imagen'])) {
                            unlink('../' . $property['imagen']);
                        }
                        $message = 'Propiedad eliminada exitosamente';
                    } else {
                        $error = 'Error al eliminar la propiedad';
                    }
                } else {
                    $error = 'No tienes permisos para eliminar esta propiedad';
                }
                break;
        }
    }
}

// Obtener propiedades del agente
$query = "SELECT * FROM propiedades WHERE agente_id = :agente_id ORDER BY fecha_creacion DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':agente_id', $_SESSION['user_id']);
$stmt->execute();
$propiedades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si se está editando, obtener datos de la propiedad
$editing_property = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_query = "SELECT * FROM propiedades WHERE id = :id AND agente_id = :agente_id";
    $edit_stmt = $db->prepare($edit_query);
    $edit_stmt->bindParam(':id', $edit_id);
    $edit_stmt->bindParam(':agente_id', $_SESSION['user_id']);
    $edit_stmt->execute();
    
    if ($edit_stmt->rowCount() > 0) {
        $editing_property = $edit_stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Propiedades - UTN Real Estate</title>
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
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="propiedades.php" class="active"><i class="fas fa-building"></i> Mis Propiedades</a></li>
                <li><a href="perfil.php"><i class="fas fa-user"></i> Mi Perfil</a></li>
                <li><a href="../index.php"><i class="fas fa-globe"></i> Ver Sitio Web</a></li>
                <li><a href="../includes/logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
            </ul>
        </nav>

        <main class="admin-content">
            <div class="admin-header">
                <h1>Gestión de Mis Propiedades</h1>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Formulario para agregar/editar propiedad -->
            <div class="form-section">
                <h2><?php echo $editing_property ? 'Editar Propiedad' : 'Agregar Nueva Propiedad'; ?></h2>
                <form method="POST" enctype="multipart/form-data" class="admin-form">
                    <input type="hidden" name="action" value="<?php echo $editing_property ? 'edit' : 'add'; ?>">
                    <?php if ($editing_property): ?>
                        <input type="hidden" name="id" value="<?php echo $editing_property['id']; ?>">
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="titulo">Título:</label>
                            <input type="text" id="titulo" name="titulo" required 
                                   value="<?php echo $editing_property ? htmlspecialchars($editing_property['titulo']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="precio">Precio:</label>
                            <input type="number" id="precio" name="precio" required 
                                   value="<?php echo $editing_property ? $editing_property['precio'] : ''; ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="tipo">Tipo:</label>
                            <select id="tipo" name="tipo" required>
                                <option value="venta" <?php echo ($editing_property && $editing_property['tipo'] === 'venta') ? 'selected' : ''; ?>>Venta</option>
                                <option value="alquiler" <?php echo ($editing_property && $editing_property['tipo'] === 'alquiler') ? 'selected' : ''; ?>>Alquiler</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="ubicacion">Ubicación:</label>
                            <input type="text" id="ubicacion" name="ubicacion" 
                                   value="<?php echo $editing_property ? htmlspecialchars($editing_property['ubicacion']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción Breve:</label>
                        <textarea id="descripcion" name="descripcion" required><?php echo $editing_property ? htmlspecialchars($editing_property['descripcion']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="descripcion_larga">Descripción Completa:</label>
                        <textarea id="descripcion_larga" name="descripcion_larga" rows="5"><?php echo $editing_property ? htmlspecialchars($editing_property['descripcion_larga']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="mapa">URL del Mapa:</label>
                        <input type="url" id="mapa" name="mapa" 
                               value="<?php echo $editing_property ? htmlspecialchars($editing_property['mapa']) : ''; ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="imagen">Imagen:</label>
                            <input type="file" id="imagen" name="imagen" accept="image/*">
                            <?php if ($editing_property && $editing_property['imagen']): ?>
                                <div style="margin-top: 10px;">
                                    <img src="../<?php echo htmlspecialchars($editing_property['imagen']); ?>" 
                                         alt="Imagen actual" style="max-width: 200px; height: auto;">
                                    <p><small>Imagen actual</small></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="destacada" value="1" 
                                       <?php echo ($editing_property && $editing_property['destacada']) ? 'checked' : ''; ?>>
                                Marcar como destacada
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $editing_property ? 'Actualizar Propiedad' : 'Agregar Propiedad'; ?>
                        </button>
                        <?php if ($editing_property): ?>
                            <a href="propiedades.php" class="btn btn-secondary">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Lista de propiedades -->
            <div class="table-section">
                <h2>Mis Propiedades</h2>
                <div class="table-container">
                    <table class="admin-table">
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
                            <?php if (empty($propiedades)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">No tienes propiedades registradas</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($propiedades as $propiedad): ?>
                                    <tr>
                                        <td>
                                            <?php if ($propiedad['imagen']): ?>
                                                <img src="../<?php echo htmlspecialchars($propiedad['imagen']); ?>" 
                                                     alt="Propiedad" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                            <?php else: ?>
                                                <div style="width: 60px; height: 60px; background: #ddd; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
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
                                        <td><?php echo htmlspecialchars($propiedad['ubicacion']); ?></td>
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
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de eliminar esta propiedad?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $propiedad['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
