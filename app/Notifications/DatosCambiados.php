<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DatosCambiados extends Notification
{
    use Queueable;

    protected $user;
    protected $cambios;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $cambios)
    {
        $this->user = $user;
        $this->cambios = $cambios;
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
        $mailMessage = (new MailMessage)
        ->subject('Datos del Usuario Actualizados')
        ->greeting(' ')
        ->line('El usuario ' . $this->user->nombres . ' ' . $this->user->apellidos . ' desea actualizar sus datos.')
        ->line('Como administrador, usted debe evaluar si los siguientes datos serán actualizados.')
        ->line('De estar conforme, deberá realizarlo mediante la opción de Gestión de Jugadores en el sistema.');

        // Crear la tabla en HTML con fondo blanco y texto negro
        $table = '<table style="width:100%; border-collapse: collapse;">';
        $table .= '<tr><th style="border: 1px solid #ddd; padding: 8px;">Campo</th><th style="border: 1px solid #ddd; padding: 8px; ">Antes</th><th style="border: 1px solid #ddd; padding: 8px; ">Después</th></tr>';

        foreach ($this->cambios as $campo => $valores) {
            $table .= '<tr>';
            $table .= '<td style="border: 1px solid #ddd; padding: 8px; ">' . $this->formatFieldName($campo) . '</td>';
            $table .= '<td style="border: 1px solid #ddd; padding: 8px; ">' . $valores['old'] . '</td>';
            $table .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $valores['new'] . '</td>';
            $table .= '</tr>';
        }

        $table .= '</table>';

        $mailMessage->line(new \Illuminate\Support\HtmlString($table))
            ->line(new \Illuminate\Support\HtmlString('<br>')) // Salto de línea
            ->line('Gracias por usar nuestra aplicación!')
            ->salutation(new \Illuminate\Support\HtmlString('Saludos,<br>Equipo de Soporte'));


        return $mailMessage;
    }

    /**
     * Formatea los nombres de los campos para que sean más legibles.
     *
     * @param  string  $field
     * @return string
     */
    protected function formatFieldName($field)
    {
        $fields = [
            'mano_habil' => 'Mano Hábil',
            'fecha_nacimiento' => 'Fecha de Nacimiento',
            'marca_raqueta' => 'Marca de Raqueta',
            'categoria_id' => 'Categoría',
            'imagen_path' => 'Imagen',
            'nombres' => 'Nombres',
            'apellidos' => 'Apellidos',
            'tipo_documento_id' => 'Tipo de Documento',
            'nro_documento' => 'Número de Documento',
            'edad' => 'Edad',
            'sexo' => 'Sexo',
            'telefono' => 'Teléfono',
            'celular' => 'Celular',
            'altura' => 'Altura',
            'peso' => 'Peso',
            'password' => 'Contraseña'
        ];

        return $fields[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }
}