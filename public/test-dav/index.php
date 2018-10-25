<?php

var_dump(mail("davidbdjt@gmail.com","sujet","msg"));

/*
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__  . '/../../vendor/autoload.php';

$from="contact@culturaccess.com";

$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'royalrajput9329@gmail.com';                 // SMTP username
$mail->Password = 'thakurajay9329';                           // SMTP password
$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 587;                                    // TCP port to connect to

//Recipients
$mail->setFrom($to, 'ISRAEL ACCESS');
$mail->addAddress('ajaythakur9329@outlook.com', 'Ajay Thakur');
if($from !='')
{
    $mail->addAddress($from);				// Add a recipient
}
*/