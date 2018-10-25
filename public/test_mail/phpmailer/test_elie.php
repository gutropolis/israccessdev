<?php

require('PHPMailerAutoload.php');
$guser = 'contact@culturaccess.com'; // username
    $gpass = 'bexDfY85pqRsAd'; //password
    $from = "contact@culturaccess.com";
    $to = "gutropolis@gmail.com";
    $subject ="email de phpmailer ce matin 10 juillet";
    $body = "ca va?";    
    $from_name = "cultraccess";
    global $error;
    $mail = new PHPMailer();  // create a new object
    $mail->IsSMTP(); // enable SMTP
    $mail->SMTPDebug = 1;  // debugging: 1 = errors and messages, 2 = messages only
    $mail->SMTPAuth = true;  // authentication enabled
    //$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for 
    $mail->Host = 'pro1.mail.ovh.net';
    $mail->Port = 587; 
    $mail->IsHTML(true);
    $mail->Username = $guser;  
    $mail->Password = $gpass;           
    $mail->From=$from;
    $mail->FromName = $from_name;
    $mail->Subject = $subject;
    $mail->Body = $body;
    $mail->CharSet = 'UTF-8';
    $mail->AddAddress($to);
   /* $mail->AddBCC($mail_aleph, "copie");
    $mail->AddBCC($mail_allan, "copie");*/
    $mail->AddReplyTo($from, $name =$from_name);
    $mail->SetFrom($from, $name = $from_name);

    if(!$mail->Send()) {
        //$error = 'Mail error: '.$mail->ErrorInfo; 
        return false;
    } else {
        //$error = 'Message sent!';
        return true;
    }
