<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre   = trim($_POST['nombre'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $mensaje  = trim($_POST['mensaje'] ?? '');

    // Validaciones básicas
    if ($nombre === '' || $email === '' || $mensaje === '') {
        echo "<script>alert('Por favor complete todos los campos requeridos'); window.location.href='index.php#contacto';</script>";
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('El correo electrónico no es válido'); window.location.href='index.php#contacto';</script>";
        exit;
    }

    // Configuración del correo
    $to      = "info@utnrealestate.com"; // Cambia por tu correo real
    $subject = "Nuevo mensaje desde el sitio web";
    $body    = "Has recibido un nuevo mensaje:\n\n".
               "Nombre: $nombre\n".
               "Email: $email\n".
               "Teléfono: $telefono\n\n".
               "Mensaje:\n$mensaje\n";

    $headers = "From: no-reply@utnrealestate.com\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Enviar
    if (mail($to, $subject, $body, $headers)) {
        echo "<script>alert('✅ Mensaje enviado correctamente'); window.location.href='index.php#contacto';</script>";
    } else {
        echo "<script>alert('❌ Error al enviar el mensaje, inténtelo más tarde'); window.location.href='index.php#contacto';</script>";
    }
} else {
    header('Location: index.php');
    exit;
}
?>