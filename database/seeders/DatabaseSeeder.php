<?php

namespace Database\Seeders;

use App\Models\RedSocial;
use App\Models\RegistroVista;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Usuario::firstOrCreate(
            ['email' => 'admin@socialmetrics.com'],
            [
                'nombre' => 'Administrador Principal',
                'password' => bcrypt('admin123'),
                'rol' => 'admin',
                'telefono' => '+52 555 123 4567',
                'avatar' => 'https://ui-avatars.com/api/?name=Admin+Principal&background=dc2743&color=fff&size=128',
            ]
        );

        $clientes = [
            ['nombre' => 'Maria Fernanda Lopez', 'email' => 'maria@socialmetrics.com', 'telefono' => '+52 555 234 5678'],
            ['nombre' => 'Carlos Andres Ruiz', 'email' => 'carlos@socialmetrics.com', 'telefono' => '+52 555 345 6789'],
            ['nombre' => 'Sofia Martinez Vega', 'email' => 'sofia@socialmetrics.com', 'telefono' => '+52 555 456 7890'],
            ['nombre' => 'Diego Alejandro Cruz', 'email' => 'diego@socialmetrics.com', 'telefono' => '+52 555 567 8901'],
            ['nombre' => 'Valentina Gomez Silva', 'email' => 'valentina@socialmetrics.com', 'telefono' => '+52 555 678 9012'],
        ];

        foreach ($clientes as $cliente) {
            Usuario::firstOrCreate(
                ['email' => $cliente['email']],
                [
                    'nombre' => $cliente['nombre'],
                    'password' => bcrypt('cliente123'),
                    'rol' => 'cliente',
                    'telefono' => $cliente['telefono'],
                    'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($cliente['nombre']) . '&background=6366f1&color=fff&size=128',
                ]
            );
        }

        $redes = [
            ['nombre' => 'Instagram', 'icono' => 'instagram', 'color' => '#bc1888', 'activa' => true],
            ['nombre' => 'Facebook', 'icono' => 'facebook', 'color' => '#1877F2', 'activa' => true],
            ['nombre' => 'TikTok', 'icono' => 'tiktok', 'color' => '#000000', 'activa' => true],
            ['nombre' => 'YouTube', 'icono' => 'youtube', 'color' => '#FF0000', 'activa' => true],
            ['nombre' => 'X (Twitter)', 'icono' => 'x-twitter', 'color' => '#000000', 'activa' => true],
            ['nombre' => 'WhatsApp', 'icono' => 'whatsapp', 'color' => '#25D366', 'activa' => true],
            ['nombre' => 'LinkedIn', 'icono' => 'linkedin', 'color' => '#0A66C2', 'activa' => true],
            ['nombre' => 'Pinterest', 'icono' => 'pinterest', 'color' => '#E60023', 'activa' => true],
            ['nombre' => 'Telegram', 'icono' => 'telegram', 'color' => '#0088cc', 'activa' => true],
            ['nombre' => 'Spotify', 'icono' => 'spotify', 'color' => '#1DB954', 'activa' => true],
        ];

        foreach ($redes as $red) {
            RedSocial::firstOrCreate(
                ['nombre' => $red['nombre']],
                $red
            );
        }

        if (RegistroVista::count() === 0) {
            $this->generarRegistros($admin, $clientes);
        }
    }

    private function generarRegistros($admin, $clientes): void
    {
        $todosLosUsuarios = collect([$admin])->merge(Usuario::where('rol', 'cliente')->get());
        $redes = RedSocial::all();

        $tipos = ['imagen', 'video', 'texto', 'stories', 'reel', 'live', 'podcast'];
        $titulos = [
            'Publicacion promocional de temporada',
            'Video tutorial rapido',
            'Sorteo de seguidores',
            'Detras de camaras del lanzamiento',
            'Consejos de uso del producto',
            'Anuncio de nueva coleccion',
            'Respuesta a preguntas frecuentes',
            'Colaboracion con influencer',
            'Lanzamiento de producto especial',
            'Stories del dia promocional',
            'Reel con tendencia musical',
            'Transmision en vivo de evento',
            'Publicacion de agradecimiento',
            'Sneak peak del proximo producto',
            'Encuesta para la comunidad',
            'Testimonios de clientes',
            'Consejo del dia',
            'Historia del viaje',
            'Precio especial por tiempo limitado',
            'Receta patrocinada',
        ];

        $descripciones = [
            'Contenido publicado con estrategia de engagement para incrementar la participacion de la comunidad.',
            'Publicacion creada como parte de la campana de visibilidad mensual de la marca.',
            'Material promocional con llamada a la accion para generar visitas al perfil.',
            'Contenido viral con alto potencial de compartidos y comentarios.',
            'Publicacion informativa con datos relevantes para la audiencia objetivo.',
            'Material audiovisual producido para reforzar el posicionamiento digital.',
            'Publicacion con promocion cruzada entre las plataformas de la marca.',
            'Contenido interactivo disenado para aumentar el alcance organico.',
        ];

        for ($i = 1; $i <= 60; $i++) {
            $usuario = $todosLosUsuarios->random();
            $red = $redes->random();
            $fecha = now()->subDays(rand(0, 180));

            $baseVistas = $red->nombre == 'TikTok' ? rand(20000, 250000) : rand(5000, 80000);
            $vistas = $baseVistas + (($i * 137) % 10000);
            $likes = intval($vistas * rand(3, 12) / 100);
            $comentarios = intval($likes * rand(1, 6) / 100);
            $compartidos = intval($likes * rand(1, 8) / 100);

            RegistroVista::create([
                'usuario_id' => $usuario->id,
                'red_social_id' => $red->id,
                'titulo' => $titulos[array_rand($titulos)] . ' #' . $i,
                'descripcion' => $descripciones[array_rand($descripciones)],
                'vistas' => $vistas,
                'likes' => $likes,
                'comentarios' => $comentarios,
                'compartidos' => $compartidos,
                'estado' => ['activo', 'activo', 'activo', 'inactivo', 'borrador'][rand(0, 4)],
                'tipo_contenido' => $tipos[array_rand($tipos)],
                'url_contenido' => 'https://www.' . strtolower($red->nombre == 'X (Twitter)' ? 'twitter.com' : $red->nombre) . '.com/share/' . rand(100000, 999999),
                'fecha_registro' => $fecha->format('Y-m-d'),
                'created_at' => $fecha,
            ]);
        }
    }
}