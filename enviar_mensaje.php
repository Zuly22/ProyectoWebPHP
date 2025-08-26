<?php

//para enviar email 
//instalar composer
//en la ubicación del proyecto en cmd correr el comando: composer require phpmailer/phpmailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre   = trim($_POST['nombre'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $mensaje  = trim($_POST['mensaje'] ?? '');

    if ($nombre === '' || $email === '' || $mensaje === '') {
        echo "<script>alert('Por favor complete todos los campos requeridos'); window.location.href='index.php#contacto';</script>";
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('El correo electrónico no es válido'); window.location.href='index.php#contacto';</script>";
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'inmobiliariopro02@gmail.com';
        $mail->Password   = 'vktf xmtj alho yqfp';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('inmobiliariopro02@gmail.com', 'Inmobiliaria S.A.');
        
        $mail->addAddress("info@utninmobiliaria.com", "Admin UTN Inmobiliaria");
        $mail->addReplyTo($email, $nombre); 
        $mail->addBCC($email, $nombre); 

        $mail->isHTML(false); 
        $mail->Subject = "Nuevo mensaje desde el sitio web";
        $mail->Body    = "Has recibido un nuevo mensaje:\n\n".
                         "Nombre: $nombre\n".
                         "Email: $email\n".
                         "Teléfono: $telefono\n\n".
                         "Mensaje:\n$mensaje\n";

        $mail->send();
        echo "<script>alert('✅ Mensaje enviado correctamente'); window.location.href='index.php#contacto';</script>";
    } catch (Exception $e) {
        echo "<script>alert('❌ Error al enviar el mensaje: {$mail->ErrorInfo}'); window.location.href='index.php#contacto';</script>";
    }
} else {
    header('Location: index.php');
    exit;
}
?>
ñ