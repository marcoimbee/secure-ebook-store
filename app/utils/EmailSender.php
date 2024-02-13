<?php

namespace App\Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Utils\Logger;


class EmailSender {

    private static function sendEmail($email, $emailSubject, $emailBody){
        $mailObj = new PHPMailer(true);
        try {
            $mailObj->isSMTP();
            $mailObj->Host = 'smtp.gmail.com';
            $mailObj->SMTPAuth = true;
            $mailObj->Username = getenv('gmail_email');  
            $mailObj->Password = getenv('gmail_password');  
            $mailObj->SMTPSecure = 'tls';
            $mailObj->Port = 587;    
            $mailObj->setFrom(getenv('gmail_email'), 'Book Store');
            $mailObj->addAddress($email);
            $mailObj->Subject = $emailSubject;
            $mailObj->Body = $emailBody;
            $mailObj->send();
            return true;
        } catch (Exception $e) {
            Logger::error("An exception occurred while sending an email " . $e->getMessage());
            return false;
        }
    }

    public static function sendProfileUpdateNotification($email){
        $emailSubject = 'Profile Update';
        $emailBody = 'Your profile has been successfully updated.';
        
        return self::sendEmail($email, $emailSubject, $emailBody);
    }

    public static function sendPasswordChangedConfirmation($email) {
        $emailSubject = 'Password Update';
        $emailBody = 'Your password has been successfully updated.';
        
        return self::sendEmail($email, $emailSubject, $emailBody);
    }

    public static function sendResetPasswordEmail($email, $token) {
        $emailSubject = 'Password Reset';

        // Compose the email body with the reset link containing the token
        $resetLink = "https://localhost/resetPassword?token=$token";
        $emailBody = "Click the following link to reset your password: $resetLink"; 
        
        return self::sendEmail($email, $emailSubject, $emailBody);
    }
}
