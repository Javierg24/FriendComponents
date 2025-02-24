<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");


require '../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    $email = $data['email'];
    $productos = $data['productos'];  // Recibe los productos del carrito
    $total = $data['total'];  // Recibe el total

    $subject = "Confirmación de Pedido";
    $body = "Hola, tu pedido ha sido recibido con éxito.\n\nDetalles del pedido:\n";

    // Construir el mensaje con los productos
    $mensaje = "<h2>Gracias por tu compra</h2>";
    $mensaje .= "<p>Tu pedido ha sido confirmado con éxito. Aquí están los detalles:</p>";
    $mensaje .= "<ul>";
    foreach ($productos as $producto) {
        $mensaje .= "<li>{$producto['nombre']} - {$producto['cantidad']} x {$producto['precio']}€</li>";
    }
    $mensaje .= "</ul>";
    $mensaje .= "<h3>Total: {$total}€</h3>";
    $mensaje .= "<p>Esperamos verte de nuevo pronto.</p>";

    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Servidor SMTP (Gmail: smtp.gmail.com)
        $mail->SMTPAuth = true;
        $mail->Username = 'friendcomponents@gmail.com'; // Tu correo
        $mail->Password = 'A1234567.'; // Tu contraseña
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS o SSL
        $mail->Port = 587; // Puerto para TLS (465 para SSL)

        // Configuración del remitente y destinatario
        $mail->setFrom('friendcomponents@gmail.com', 'FriendComponents');
        $mail->addAddress($email);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $mensaje;

        // Enviar correo
        if ($mail->send()) {
            echo json_encode(["message" => "Correo enviado con éxito"]);
        } else {
            echo json_encode(["message" => "Error al enviar correo"]);
        }

    } catch (Exception $e) {
        echo json_encode(["message" => "Error al enviar el correo: " . $mail->ErrorInfo]);
    }
} else {
    echo json_encode(["message" => "Método no permitido"]);
}
?>
