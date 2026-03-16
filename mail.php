<?php
include 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = urldecode($_POST['name']);
    $email = urldecode($_POST['email']);
    $subject = urldecode($_POST['subject']);
    $message = urldecode($_POST['message']);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.office365.com';
        $mail->SMTPAuth = true;

        $mail->Username = 'V1A.GRW.HTML@outlook.cz';
        $mail->Password = ''; //heslo

        $mail->setFrom('V1A.GRW.HTML@outlook.cz', 'V1A.GRW.HTML');

        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = "UTF-8";

        $mail->addAddress($email);

        $mail->Subject = $subject;
        $mail->Body = $name . " posílá zprávu: \n\n" . $message;
        $mail->AltBody = $message;

        $mail->send();
        echo 'E-mail byl úspěšně odeslán.<br>';
        echo 'Předmět: ' . $subject . '<br>';
        echo 'Jméno odesílatele: ' . $name . '<br>';
        echo 'Zpráva: ' . $message . '<br>';
    } catch (Exception $e) {
        echo 'E-mail nemohl být odeslán. Chyba: ', $mail->ErrorInfo;
    }
} else {
    echo 'Neplatná metoda požadavku.';
}
