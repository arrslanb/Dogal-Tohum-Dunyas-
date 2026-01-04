<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        // SMTP Ayarları (Hostinger, Gmail vb. bilgilerini buraya gir)
        $mail->isSMTP();
        $mail->Host       = 'smtp.domaininiz.com'; // SMTP sunucusu
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@dogaltohumlar.com'; // Mail adresin
        $mail->Password   = 'MailSifren123'; // Mail şifren
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Alıcı ve Gönderici
        $mail->setFrom('info@dogaltohumlar.com', 'Doğal Tohum Dünyası');
        $mail->addAddress($to);

        // İçerik
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}