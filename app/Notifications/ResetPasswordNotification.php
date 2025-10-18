<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail']; // enviará un correo
    }

 public function toMail($notifiable)
{
    // La URL base de tu app donde se encuentra la pantalla de reset
    $frontendUrl = env('FRONTEND_URL', 'http://localhost:8081');

    // Concatenamos la ruta de la pantalla y agregamos token y email como query params
    $resetUrl = $frontendUrl . "/auth/ResetPasswordScreen?token={$this->token}&email=" . urlencode($notifiable->email);

    return (new \Illuminate\Notifications\Messages\MailMessage)
        ->subject('Restablecer contraseña')
        ->line('Haz clic en el siguiente enlace para restablecer tu contraseña:')
        ->action('Restablecer contraseña', $resetUrl)
        ->line('Si no solicitaste este cambio, ignora este correo.');
}
}
