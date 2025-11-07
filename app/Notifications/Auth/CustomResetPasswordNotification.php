<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang; // Importar Lang

class CustomResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * El token para resetear la contraseña.
     *
     * @var string
     */
    public $token;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // 1. Construimos la URL de tu frontend (Vue)
        // Usará tu APP_URL del .env (http://agrolink.local)
        $resetUrl = config('app.url') . '/reset-password'
            . '?token=' . $this->token
            . '&email=' . urlencode($notifiable->getEmailForPasswordReset());

        // 2. Creamos el mensaje de correo
        return (new MailMessage)
            ->subject(Lang::get('Reseteo de Contraseña de Agrolink'))
            ->line(Lang::get('Estás recibiendo este correo porque solicitaste un reseteo de contraseña para tu cuenta.'))
            ->action(Lang::get('Resetear Contraseña'), $resetUrl)
            ->line(Lang::get('Este enlace de reseteo expirará en :count minutos.', ['count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')]))
            ->line(Lang::get('Si no solicitaste un reseteo de contraseña, no se requiere ninguna acción.'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
