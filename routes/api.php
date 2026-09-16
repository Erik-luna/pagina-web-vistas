<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use App\Models\Notificacion;
use App\Models\RedSocial;
use App\Models\RegistroVista;
use App\Models\Usuario;

/*
|--------------------------------------------------------------------------
| SocialMetrics REST API
|--------------------------------------------------------------------------
| Backend completo para la aplicación móvil (Flutter). Documentación visual:
|
|   GET {base}/api/docs              -> Swagger UI (HTML)
|   GET {base}/api/docs/openapi.json -> especificación OpenAPI 3.0 (JSON)
|
| Autenticación: Bearer token. Ejemplo:
|   POST {base}/api/v1/auth/login  {email, password}  -> {token}
|   GET  {base}/api/v1/auth/perfil  (Header: Authorization: Bearer <token>)
|
| La tabla "api_tokens" se crea automáticamente la primera vez que el
| servidor procesa una petición a esta ruta (no requiere migraciones).
|--------------------------------------------------------------------------
*/

// ============================================================
// 1. Infraestructura: tabla de tokens (auto-creada)
// ============================================================
if (!Schema::hasTable('api_tokens')) {
    Schema::create('api_tokens', function ($table) {
        $table->id();
        $table->unsignedBigInteger('usuario_id');
        $table->string('token', 64)->unique();
        $table->string('nombre')->nullable();
        $table->timestamp('ultimo_uso_at')->nullable();
        $table->timestamp('expira_en')->nullable();
        $table->timestamp('created_at')->nullable();
        $table->index('usuario_id');
    });
}

// ============================================================
// 2. Funciones auxiliares globales
// ============================================================
if (!function_exists('api_json')) {
    function api_json($data = null, int $code = 200, ?string $message = null, array $extra = [])
    {
        return response()->json(array_merge([
            'success' => $code >= 200 && $code < 300,
            'message' => $message,
            'data' => $data,
        ], $extra), $code);
    }
}

if (!function_exists('api_error')) {
    function api_error(string $message, int $code = 400, $errors = null)
    {
        $payload = ['success' => false, 'message' => $message, 'data' => null];
        if ($errors) {
            $payload['errores'] = $errors;
        }
        return response()->json($payload, $code);
    }
}

if (!function_exists('api_validar')) {
    function api_validar(Request $request, array $reglas): ?JsonResponse
    {
        $validador = Validator::make($request->all(), $reglas);
        if ($validador->fails()) {
            return api_error('Los datos enviados no son válidos.', 422, $validador->errors()->toArray());
        }
        return null;
    }
}

if (!function_exists('api_usuario')) {
    function api_usuario(Request $request): ?Usuario
    {
        return $request->attributes->get('api_usuario');
    }
}

if (!function_exists('api_requiere_admin')) {
    function api_requiere_admin(Request $request)
    {
        $usuario = api_usuario($request);
        if (!$usuario || !$usuario->isAdmin()) {
            return api_error('Solo los administradores pueden realizar esta acción.', 403);
        }
        return $usuario;
    }
}

if (!function_exists('api_verificar_password')) {
    function api_verificar_password(Usuario $usuario, string $password): bool
    {
        try {
            if (Hash::check($password, $usuario->password)) {
                return true;
            }
        } catch (\Throwable $e) {
            // La contraseña almacenada no está en formato bcrypt (texto plano).
        }

        // Compatibilidad con contraseñas guardadas en texto plano
        // (usuarios de la base existente). Al acertar se convierte a bcrypt.
        if (hash_equals((string) $usuario->password, (string) $password)) {
            try {
                $usuario->password = $password;
                $usuario->save();
            } catch (\Throwable $e) {
                // no bloquea el login
            }
            return true;
        }
        return false;
    }
}

if (!function_exists('api_generar_token')) {
    function api_generar_token(Usuario $usuario, ?string $nombre, Request $request): string
    {
        $token = bin2hex(random_bytes(32));
        DB::table('api_tokens')->insert([
            'usuario_id' => $usuario->id,
            'token' => $token,
            'nombre' => $nombre ?: 'App móvil',
            'ultimo_uso_at' => now(),
            'expira_en' => now()->addDays(30),
            'created_at' => now(),
        ]);
        return $token;
    }
}

if (!function_exists('api_tabla_tiene_columna')) {
    function api_tabla_tiene_columna(string $tabla, string $columna): bool
    {
        try {
            return Schema::hasColumn($tabla, $columna);
        } catch (\Throwable $e) {
            return false;
        }
    }
}

if (!function_exists('api_formatear_usuario')) {
    function api_formatear_usuario(Usuario $u): array
    {
        return [
            'id' => $u->id,
            'nombre' => $u->nombre,
            'email' => $u->email,
            'rol' => $u->rol,
            'avatar' => $u->avatar,
            'telefono' => $u->telefono,
            'activo' => (bool) $u->activo,
            'total_registros' => $u->registros_count ?? 0,
            'created_at' => $u->created_at ? $u->created_at->toDateTimeString() : null,
        ];
    }
}

if (!function_exists('api_formatear_red')) {
    function api_formatear_red(RedSocial $r): array
    {
        return [
            'id' => $r->id,
            'nombre' => $r->nombre,
            'icono' => $r->icono,
            'color' => $r->color,
            'url_logo' => $r->url_logo,
            'activa' => (bool) $r->activa,
            'total_registros' => $r->registros_count ?? 0,
            'created_at' => $r->created_at ? $r->created_at->toDateTimeString() : null,
        ];
    }
}

if (!function_exists('api_formatear_registro')) {
    function api_formatear_registro(RegistroVista $r): array
    {
        return [
            'id' => $r->id,
            'usuario_id' => $r->usuario_id,
            'usuario' => $r->usuario ? $r->usuario->nombre : null,
            'red_social_id' => $r->red_social_id,
            'red_social' => $r->redSocial ? $r->redSocial->nombre : null,
            'icono_red' => $r->redSocial ? $r->redSocial->icono : null,
            'color_red' => $r->redSocial ? $r->redSocial->color : null,
            'titulo' => $r->titulo,
            'descripcion' => $r->descripcion,
            'vistas' => (int) $r->vistas,
            'likes' => (int) $r->likes,
            'comentarios' => (int) $r->comentarios,
            'compartidos' => (int) $r->compartidos,
            'estado' => $r->estado,
            'tipo_contenido' => $r->tipo_contenido,
            'url_contenido' => $r->url_contenido,
            'fecha_registro' => $r->fecha_registro ? $r->fecha_registro->format('Y-m-d') : null,
            'created_at' => $r->created_at ? $r->created_at->toDateTimeString() : null,
        ];
    }
}

if (!function_exists('api_formatear_notificacion')) {
    function api_formatear_notificacion(Notificacion $n): array
    {
        return [
            'id' => $n->id,
            'usuario_id' => $n->usuario_id,
            'titulo' => $n->titulo,
            'mensaje' => $n->mensaje,
            'tipo' => $n->tipo ?? 'info',
            'leida' => (bool) $n->leida,
            'url' => $n->url ?? null,
            'created_at' => $n->created_at ? $n->created_at->toDateTimeString() : null,
        ];
    }
}

if (!function_exists('api_paginacion')) {
    function api_paginacion($paginador): array
    {
        return [
            'total' => $paginador->total(),
            'por_pagina' => $paginador->perPage(),
            'pagina_actual' => $paginador->currentPage(),
            'ultima_pagina' => $paginador->lastPage(),
        ];
    }
}

if (!function_exists('api_buscar_registro')) {
    function api_buscar_registro(Request $request, $id)
    {
        $registro = RegistroVista::with(['usuario', 'redSocial'])->find($id);
        if (!$registro) {
            return api_error('Registro no encontrado.', 404);
        }
        $usuario = api_usuario($request);
        if (!$usuario->isAdmin() && $registro->usuario_id !== $usuario->id) {
            return api_error('No tienes permiso para acceder a este registro.', 403);
        }
        return $registro;
    }
}

if (!function_exists('api_generar_csv')) {
    function api_generar_csv(Illuminate\Support\Collection $registros): string
    {
        $flujo = fopen('php://temp', 'w+');
        fputcsv($flujo, [
            'ID', 'Titulo', 'Red Social', 'Tipo', 'Estado', 'Fecha',
            'Vistas', 'Likes', 'Comentarios', 'Compartidos', 'Usuario',
        ]);
        foreach ($registros as $r) {
            fputcsv($flujo, [
                $r->id,
                $r->titulo,
                $r->redSocial->nombre ?? 'N/A',
                $r->tipo_contenido,
                $r->estado,
                $r->fecha_registro ? $r->fecha_registro->format('Y-m-d') : '',
                $r->vistas,
                $r->likes,
                $r->comentarios,
                $r->compartidos,
                $r->usuario->nombre ?? 'N/A',
            ]);
        }
        rewind($flujo);
        $contenido = stream_get_contents($flujo);
        fclose($flujo);
        return $contenido;
    }
}

if (!function_exists('api_docs_html')) {
    function api_docs_html(Request $request): string
    {
        $specUrl = $request->root() . '/api/docs/openapi.json';
        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>SocialMetrics API - Documentación</title>
  <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css"/>
  <style>
    body { margin: 0; background: #f7f9fc; }
    .api-encabezado {
      background: #6366f1; color: #fff; padding: 18px 24px;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    .api-encabezado h1 { margin: 0; font-size: 20px; }
    .api-encabezado p { margin: 4px 0 0; opacity: .9; font-size: 13px; }
    .api-encabezado code {
      background: rgba(255,255,255,.2); padding: 2px 8px; border-radius: 4px;
      font-size: 12px;
    }
  </style>
</head>
<body>
  <div class="api-encabezado">
    <h1>SocialMetrics API</h1>
    <p>Especificación: <code>{$specUrl}</code> &middot; Autenticación: <code>Authorization: Bearer &lt;token&gt;</code></p>
  </div>
  <div id="swagger-ui"></div>
  <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js" charset="UTF-8"></script>
  <script>
    window.onload = function () {
      window.ui = SwaggerUIBundle({
        url: "{$specUrl}",
        dom_id: "#swagger-ui",
        deepLinking: true,
        presets: [SwaggerUIBundle.presets.apis],
        layout: "BaseLayout"
      });
    };
  </script>
</body>
</html>
HTML;
    }
}

if (!function_exists('api_docs_openapi')) {
    function api_docs_openapi(Request $request): array
    {
        $base = $request->root() . '/api/v1';

        return [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'SocialMetrics API',
                'description' => 'API REST del panel de analítica de vistas. Permite la gestión de registros de contenido, redes sociales, usuarios, notificaciones y reportes. Autenticación mediante token Bearer.',
                'version' => '1.0.0',
            ],
            'servers' => [['url' => $base]],
            'tags' => [
                ['name' => 'Autenticación', 'description' => 'Login, registro, perfil y cierre de sesión'],
                ['name' => 'Dashboard', 'description' => 'Estadísticas globales y personales'],
                ['name' => 'Registros', 'description' => 'CRUD de registros de vistas'],
                ['name' => 'Redes Sociales', 'description' => 'Redes sociales (administración)'],
                ['name' => 'Usuarios', 'description' => 'Gestión de usuarios (solo admin)'],
                ['name' => 'Notificaciones', 'description' => 'Bandeja de notificaciones'],
                ['name' => 'Reportes', 'description' => 'Generación y exportación de reportes'],
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'token',
                    ],
                ],
                'schemas' => [
                    'Respuesta' => [
                        'type' => 'object',
                        'properties' => [
                            'success' => ['type' => 'boolean'],
                            'message' => ['type' => 'string', 'nullable' => true],
                            'data' => ['nullable' => true],
                            'errores' => ['type' => 'object', 'additionalProperties' => ['type' => 'array', 'items' => ['type' => 'string']]],
                        ],
                    ],
                    'Usuario' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'nombre' => ['type' => 'string'],
                            'email' => ['type' => 'string', 'format' => 'email'],
                            'rol' => ['type' => 'string', 'enum' => ['admin', 'cliente', 'usuario']],
                            'avatar' => ['type' => 'string', 'nullable' => true],
                            'telefono' => ['type' => 'string', 'nullable' => true],
                            'activo' => ['type' => 'boolean'],
                            'total_registros' => ['type' => 'integer'],
                            'created_at' => ['type' => 'string', 'nullable' => true],
                        ],
                    ],
                    'RedSocial' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'nombre' => ['type' => 'string'],
                            'icono' => ['type' => 'string'],
                            'color' => ['type' => 'string'],
                            'url_logo' => ['type' => 'string', 'nullable' => true],
                            'activa' => ['type' => 'boolean'],
                            'total_registros' => ['type' => 'integer'],
                        ],
                    ],
                    'Registro' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'usuario_id' => ['type' => 'integer'],
                            'usuario' => ['type' => 'string', 'nullable' => true],
                            'red_social_id' => ['type' => 'integer'],
                            'red_social' => ['type' => 'string', 'nullable' => true],
                            'icono_red' => ['type' => 'string', 'nullable' => true],
                            'color_red' => ['type' => 'string', 'nullable' => true],
                            'titulo' => ['type' => 'string'],
                            'descripcion' => ['type' => 'string', 'nullable' => true],
                            'vistas' => ['type' => 'integer'],
                            'likes' => ['type' => 'integer'],
                            'comentarios' => ['type' => 'integer'],
                            'compartidos' => ['type' => 'integer'],
                            'estado' => ['type' => 'string', 'enum' => ['activo', 'inactivo', 'borrador']],
                            'tipo_contenido' => ['type' => 'string', 'enum' => ['imagen', 'video', 'texto', 'stories', 'reel', 'live', 'podcast']],
                            'url_contenido' => ['type' => 'string', 'nullable' => true],
                            'fecha_registro' => ['type' => 'string', 'format' => 'date'],
                            'created_at' => ['type' => 'string', 'nullable' => true],
                        ],
                    ],
                    'Notificacion' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'usuario_id' => ['type' => 'integer'],
                            'titulo' => ['type' => 'string'],
                            'mensaje' => ['type' => 'string'],
                            'tipo' => ['type' => 'string', 'enum' => ['info', 'exito', 'alerta', 'error']],
                            'leida' => ['type' => 'boolean'],
                            'url' => ['type' => 'string', 'nullable' => true],
                            'created_at' => ['type' => 'string', 'nullable' => true],
                        ],
                    ],
                    'Paginacion' => [
                        'type' => 'object',
                        'properties' => [
                            'total' => ['type' => 'integer'],
                            'por_pagina' => ['type' => 'integer'],
                            'pagina_actual' => ['type' => 'integer'],
                            'ultima_pagina' => ['type' => 'integer'],
                        ],
                    ],
                ],
            ],
            'security' => [['bearerAuth' => []]],
            'paths' => [
                '/auth/login' => [
                    'post' => [
                        'tags' => ['Autenticación'],
                        'summary' => 'Iniciar sesión',
                        'security' => [],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['email', 'password'],
                                        'properties' => [
                                            'email' => ['type' => 'string', 'format' => 'email'],
                                            'password' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Login exitoso, devuelve token y usuario'],
                            '401' => ['description' => 'Credenciales incorrectas'],
                            '422' => ['description' => 'Error de validación'],
                        ],
                    ],
                ],
                '/auth/registro' => [
                    'post' => [
                        'tags' => ['Autenticación'],
                        'summary' => 'Registrar nueva cuenta (rol cliente)',
                        'security' => [],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['nombre', 'email', 'password', 'password_confirmation'],
                                        'properties' => [
                                            'nombre' => ['type' => 'string', 'maxLength' => 100],
                                            'email' => ['type' => 'string', 'format' => 'email'],
                                            'password' => ['type' => 'string', 'minLength' => 6],
                                            'password_confirmation' => ['type' => 'string'],
                                            'telefono' => ['type' => 'string', 'nullable' => true],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => ['description' => 'Cuenta creada, devuelve token y usuario'],
                            '422' => ['description' => 'Error de validación'],
                        ],
                    ],
                ],
                '/auth/logout' => [
                    'post' => [
                        'tags' => ['Autenticación'],
                        'summary' => 'Cerrar sesión (revoca el token actual)',
                        'responses' => ['200' => ['description' => 'Sesión cerrada correctamente']],
                    ],
                ],
                '/auth/perfil' => [
                    'get' => [
                        'tags' => ['Autenticación'],
                        'summary' => 'Obtener el usuario autenticado',
                        'responses' => ['200' => ['description' => 'Datos del usuario']],
                    ],
                ],
                '/dashboard' => [
                    'get' => [
                        'tags' => ['Dashboard'],
                        'summary' => 'Estadísticas del dashboard (admin: globales; cliente: propias)',
                        'responses' => ['200' => ['description' => 'Resumen de métricas']],
                    ],
                ],
                '/registros' => [
                    'get' => [
                        'tags' => ['Registros'],
                        'summary' => 'Listar registros con filtros y paginación',
                        'parameters' => [
                            ['name' => 'buscar', 'in' => 'query', 'schema' => ['type' => 'string'], 'description' => 'Buscar por título o descripción'],
                            ['name' => 'red_social_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
                            ['name' => 'estado', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['activo', 'inactivo', 'borrador']]],
                            ['name' => 'tipo_contenido', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['imagen', 'video', 'texto', 'stories', 'reel', 'live', 'podcast']]],
                            ['name' => 'fecha_desde', 'in' => 'query', 'schema' => ['type' => 'string', 'format' => 'date']],
                            ['name' => 'fecha_hasta', 'in' => 'query', 'schema' => ['type' => 'string', 'format' => 'date']],
                            ['name' => 'por_pagina', 'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 12]],
                            ['name' => 'page', 'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 1]],
                        ],
                        'responses' => ['200' => ['description' => 'Lista de registros y paginación']],
                    ],
                    'post' => [
                        'tags' => ['Registros'],
                        'summary' => 'Crear un registro de vistas',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['red_social_id', 'titulo', 'vistas', 'likes', 'comentarios', 'compartidos', 'estado', 'tipo_contenido', 'fecha_registro'],
                                        'properties' => [
                                            'red_social_id' => ['type' => 'integer'],
                                            'titulo' => ['type' => 'string', 'maxLength' => 200],
                                            'descripcion' => ['type' => 'string'],
                                            'vistas' => ['type' => 'integer', 'minimum' => 0],
                                            'likes' => ['type' => 'integer', 'minimum' => 0],
                                            'comentarios' => ['type' => 'integer', 'minimum' => 0],
                                            'compartidos' => ['type' => 'integer', 'minimum' => 0],
                                            'estado' => ['type' => 'string', 'enum' => ['activo', 'inactivo', 'borrador']],
                                            'tipo_contenido' => ['type' => 'string', 'enum' => ['imagen', 'video', 'texto', 'stories', 'reel', 'live', 'podcast']],
                                            'url_contenido' => ['type' => 'string', 'nullable' => true],
                                            'fecha_registro' => ['type' => 'string', 'format' => 'date'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => ['description' => 'Registro creado'],
                            '422' => ['description' => 'Error de validación'],
                        ],
                    ],
                ],
                '/registros/{id}' => [
                    'get' => ['tags' => ['Registros'], 'summary' => 'Ver un registro', 'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]], 'responses' => ['200' => ['description' => 'Registro'], '404' => ['description' => 'No encontrado'], '403' => ['description' => 'Sin permiso']]],
                    'put' => ['tags' => ['Registros'], 'summary' => 'Actualizar un registro', 'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]], 'requestBody' => ['required' => true, 'content' => ['application/json' => ['schema' => ['$ref' => '#/components/schemas/Registro']]]], 'responses' => ['200' => ['description' => 'Registro actualizado'], '404' => ['description' => 'No encontrado'], '403' => ['description' => 'Sin permiso']]],
                    'patch' => ['tags' => ['Registros'], 'summary' => 'Actualizar parcialmente un registro', 'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]], 'responses' => ['200' => ['description' => 'Registro actualizado']]],
                    'delete' => ['tags' => ['Registros'], 'summary' => 'Eliminar un registro', 'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]], 'responses' => ['200' => ['description' => 'Registro eliminado'], '404' => ['description' => 'No encontrado'], '403' => ['description' => 'Sin permiso']]],
                ],
                '/redes-sociales-activas' => [
                    'get' => [
                        'tags' => ['Redes Sociales'],
                        'summary' => 'Redes sociales activas (para selectores)',
                        'responses' => ['200' => ['description' => 'Lista de redes activas']],
                    ],
                ],
                '/redes-sociales' => [
                    'get' => ['tags' => ['Redes Sociales'], 'summary' => 'Listar redes sociales (admin)', 'responses' => ['200' => ['description' => 'Lista de redes con total de registros']]],
                    'post' => ['tags' => ['Redes Sociales'], 'summary' => 'Crear red social (admin)', 'requestBody' => ['required' => true, 'content' => ['application/json' => ['schema' => ['type' => 'object', 'required' => ['nombre', 'icono', 'color'], 'properties' => ['nombre' => ['type' => 'string'], 'icono' => ['type' => 'string'], 'color' => ['type' => 'string'], 'url_logo' => ['type' => 'string', 'nullable' => true]]]]]], 'responses' => ['201' => ['description' => 'Red creada'], '422' => ['description' => 'Error de validación']]],
                ],
                '/redes-sociales/{id}' => [
                    'get' => ['tags' => ['Redes Sociales'], 'summary' => 'Ver red social (admin)', 'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]], 'responses' => ['200' => ['description' => 'Red social']]],
                    'put' => ['tags' => ['Redes Sociales'], 'summary' => 'Actualizar red social (admin) incluye activa', 'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]], 'responses' => ['200' => ['description' => 'Red actualizada']]],
                    'patch' => ['tags' => ['Redes Sociales'], 'summary' => 'Actualizar parcialmente (admin)', 'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]], 'responses' => ['200' => ['description' => 'Red actualizada']]],
                    'delete' => ['tags' => ['Redes Sociales'], 'summary' => 'Eliminar red social (admin)', 'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]], 'responses' => ['200' => ['description' => 'Red eliminada']]],
                ],
                '/usuarios' => [
                    'get' => ['tags' => ['Usuarios'], 'summary' => 'Listar usuarios con filtros (admin)', 'parameters' => [
                        ['name' => 'buscar', 'in' => 'query', 'schema' => ['type' => 'string']],
                        ['name' => 'rol', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['admin', 'cliente', 'usuario']]],
                        ['name' => 'activo', 'in' => 'query', 'schema' => ['type' => 'boolean']],
                        ['name' => 'page', 'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 1]],
                    ], 'responses' => ['200' => ['description' => 'Lista de usuarios y paginación']]],
                    'post' => ['tags' => ['Usuarios'], 'summary' => 'Crear usuario (admin)', 'requestBody' => ['required' => true, 'content' => ['application/json' => ['schema' => ['type' => 'object', 'required' => ['nombre', 'email', 'password', 'password_confirmation', 'rol'], 'properties' => ['nombre' => ['type' => 'string'], 'email' => ['type' => 'string'], 'password' => ['type' => 'string', 'minLength' => 6], 'password_confirmation' => ['type' => 'string'], 'rol' => ['type' => 'string', 'enum' => ['admin', 'cliente', 'usuario']], 'telefono' => ['type' => 'string', 'nullable' => true]]]]]], 'responses' => ['201' => ['description' => 'Usuario creado']]],
                ],
                '/usuarios/{id}' => [
                    'get' => ['tags' => ['Usuarios'], 'summary' => 'Ver usuario (admin)', 'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]], 'responses' => ['200' => ['description' => 'Usuario']]],
                    'put' => ['tags' => ['Usuarios'], 'summary' => 'Actualizar usuario (admin). Password opcional si se rellena.', 'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]], 'requestBody' => ['content' => ['application/json' => ['schema' => ['type' => 'object', 'required' => ['nombre', 'email', 'rol', 'activo'], 'properties' => ['nombre' => ['type' => 'string'], 'email' => ['type' => 'string'], 'password' => ['type' => 'string', 'minLength' => 6], 'rol' => ['type' => 'string', 'enum' => ['admin', 'cliente', 'usuario']], 'telefono' => ['type' => 'string'], 'activo' => ['type' => 'boolean']]]]]], 'responses' => ['200' => ['description' => 'Usuario actualizado']]],
                    'patch' => ['tags' => ['Usuarios'], 'summary' => 'Actualizar parcialmente (admin)', 'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]], 'responses' => ['200' => ['description' => 'Usuario actualizado']]],
                    'delete' => ['tags' => ['Usuarios'], 'summary' => 'Eliminar usuario (admin). No permite eliminar la propia cuenta.', 'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]], 'responses' => ['200' => ['description' => 'Usuario eliminado'], '409' => ['description' => 'No puedes eliminar tu propia cuenta']]],
                ],
                '/usuarios/{id}/toggle' => [
                    'post' => ['tags' => ['Usuarios'], 'summary' => 'Activar/desactivar usuario (admin). No permite desactivar la propia cuenta.', 'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]], 'responses' => ['200' => ['description' => 'Estado actualizado'], '409' => ['description' => 'No puedes desactivar tu propia cuenta']]],
                ],
                '/notificaciones' => [
                    'get' => ['tags' => ['Notificaciones'], 'summary' => 'Listar mis notificaciones (20 por página)', 'parameters' => [['name' => 'page', 'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 1]]], 'responses' => ['200' => ['description' => 'Lista y paginación']]],
                ],
                '/notificaciones/contador' => [
                    'get' => ['tags' => ['Notificaciones'], 'summary' => 'Total de notificaciones no leídas (badge)', 'responses' => ['200' => ['description' => '{\"no_leidas\": n}']]],
                ],
                '/notificaciones/{id}/leer' => [
                    'post' => ['tags' => ['Notificaciones'], 'summary' => 'Marcar una notificación como leída', 'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]], 'responses' => ['200' => ['description' => 'Marcada como leída']]],
                ],
                '/notificaciones/marcar-todas-leidas' => [
                    'post' => ['tags' => ['Notificaciones'], 'summary' => 'Marcar todas como leídas', 'responses' => ['200' => ['description' => 'Correcto']]],
                ],
                '/reportes/parametros' => [
                    'get' => ['tags' => ['Reportes'], 'summary' => 'Parámetros disponibles (redes activas y, si es admin, usuarios activos)', 'responses' => ['200' => ['description' => 'Filtros para el reporte']]],
                ],
                '/reportes/generar' => [
                    'post' => ['tags' => ['Reportes'], 'summary' => 'Generar reporte filtrado',
                        'requestBody' => ['required' => true, 'content' => ['application/json' => ['schema' => ['type' => 'object', 'required' => ['fecha_desde', 'fecha_hasta'], 'properties' => [
                            'fecha_desde' => ['type' => 'string', 'format' => 'date'],
                            'fecha_hasta' => ['type' => 'string', 'format' => 'date'],
                            'red_social_id' => ['type' => 'integer'],
                            'usuario_filtro' => ['type' => 'integer', 'description' => 'Solo admin'],
                            'estado' => ['type' => 'string', 'enum' => ['activo', 'inactivo', 'borrador']],
                            'tipo_contenido' => ['type' => 'string', 'enum' => ['imagen', 'video', 'texto', 'stories', 'reel', 'live', 'podcast']],
                        ]]]]],
                        'responses' => ['200' => ['description' => 'Resumen + registros del reporte'], '422' => ['description' => 'Error de validación']]],
                ],
                '/reportes/exportar/{formato}' => [
                    'get' => ['tags' => ['Reportes'], 'summary' => 'Exportar reporte (csv funciona; pdf/excel próximamente)', 'parameters' => [
                        ['name' => 'formato', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string', 'enum' => ['csv', 'pdf', 'excel']]],
                        ['name' => 'fecha_desde', 'in' => 'query', 'required' => true, 'schema' => ['type' => 'string', 'format' => 'date']],
                        ['name' => 'fecha_hasta', 'in' => 'query', 'required' => true, 'schema' => ['type' => 'string', 'format' => 'date']],
                        ['name' => 'red_social_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
                        ['name' => 'usuario_filtro', 'in' => 'query', 'schema' => ['type' => 'integer']],
                        ['name' => 'estado', 'in' => 'query', 'schema' => ['type' => 'string']],
                        ['name' => 'tipo_contenido', 'in' => 'query', 'schema' => ['type' => 'string']],
                    ], 'responses' => ['200' => ['description' => 'Archivo CSV'], '501' => ['description' => 'Formato no disponible']]],
                ],
            ],
        ];
    }
}

// ============================================================
// 3. Rutas
// ============================================================

// --- Documentación (público) ---
Route::get('/docs', function (Request $request) {
    return response(api_docs_html($request))->header('Content-Type', 'text/html; charset=UTF-8');
});

Route::get('/docs/openapi.json', function (Request $request) {
    return response()->json(api_docs_openapi($request));
});

// --- Health check (público) ---
Route::get('/health', function () {
    return api_json(['estado' => 'ok', 'hora' => now()->toISOString()]);
});

// --- API v1 ---
Route::prefix('v1')->group(function () {

    // Info general
    Route::get('/', function (Request $request) {
        return api_json([
            'nombre' => 'SocialMetrics API v1',
            'documentacion' => $request->getSchemeAndHttpHost() . '/api/docs',
            'autenticacion' => 'POST /api/v1/auth/login',
        ]);
    });

    // ---------- Autenticación pública ----------
    Route::post('/auth/login', function (Request $request) {
        $error = api_validar($request, [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        if ($error) {
            return $error;
        }

        $usuario = Usuario::where('email', $request->email)->where('activo', true)->first();

        if (!$usuario || !api_verificar_password($usuario, $request->password)) {
            return api_error('Credenciales incorrectas o cuenta desactivada.', 401);
        }

        $token = api_generar_token($usuario, 'App móvil - Flutter', $request);

        return api_json([
            'token' => $token,
            'usuario' => api_formatear_usuario($usuario),
        ], 200, "Bienvenido/a, {$usuario->nombre}!");
    });

    Route::post('/auth/registro', function (Request $request) {
        $error = api_validar($request, [
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:usuarios,email',
            'password' => 'required|string|min:6',
            'password_confirmation' => 'required|string|same:password',
            'telefono' => 'nullable|string|max:20',
        ]);
        if ($error) {
            return $error;
        }

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'password' => $request->password,
            'rol' => 'cliente',
            'telefono' => $request->telefono,
            'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($request->nombre) . '&background=6366f1&color=fff&size=128',
        ]);

        $token = api_generar_token($usuario, 'App móvil - Flutter', $request);

        return api_json([
            'token' => $token,
            'usuario' => api_formatear_usuario($usuario),
        ], 201, 'Cuenta creada exitosamente. Bienvenido/a!');
    });

    // ---------- Rutas protegidas (Bearer token) ----------
    Route::middleware(['auth-api'])->group(function () {

        // Cerrar sesión
        Route::post('/auth/logout', function (Request $request) {
            DB::table('api_tokens')->where('id', $request->attributes->get('api_token_id'))->delete();
            return api_json(null, 200, 'Sesión cerrada correctamente.');
        });

        // Perfil actual
        Route::get('/auth/perfil', function (Request $request) {
            return api_json(api_formatear_usuario(api_usuario($request)));
        });

        // ---------- Dashboard ----------
        Route::get('/dashboard', function (Request $request) {
            $usuario = api_usuario($request);
            $esAdmin = $usuario->isAdmin();
            $usuarioId = $esAdmin ? null : $usuario->id;

            $scope = function ($consulta) use ($usuarioId) {
                if ($usuarioId) {
                    $consulta->where('usuario_id', $usuarioId);
                }
                return $consulta;
            };

            $datos = [
                'total_registros' => $scope(RegistroVista::query())->count(),
                'total_vistas' => $scope(RegistroVista::query())->sum('vistas'),
                'total_likes' => $scope(RegistroVista::query())->sum('likes'),
                'total_comentarios' => $scope(RegistroVista::query())->sum('comentarios'),
                'total_compartidos' => $scope(RegistroVista::query())->sum('compartidos'),
                'registros_activos' => $scope(RegistroVista::query())->where('estado', 'activo')->count(),
                'registros_hoy' => $scope(RegistroVista::query())->whereDate('fecha_registro', today())->count(),
                'por_red' => RedSocial::where('activa', true)->get()->map(function ($red) use ($usuarioId) {
                    $base = RegistroVista::query()->where('red_social_id', $red->id);
                    if ($usuarioId) {
                        $base->where('usuario_id', $usuarioId);
                    }
                    return [
                        'id' => $red->id,
                        'red' => $red->nombre,
                        'icono' => $red->icono,
                        'color' => $red->color,
                        'total_registros' => $base->count(),
                        'total_vistas' => (clone $base)->sum('vistas'),
                        'total_likes' => (clone $base)->sum('likes'),
                    ];
                })->values(),
                'ultimos_registros' => $scope(RegistroVista::query()->with(['usuario', 'redSocial']))
                    ->latest('fecha_registro')
                    ->limit($esAdmin ? 10 : 8)
                    ->get()
                    ->map(function ($r) {
                        return api_formatear_registro($r);
                    }),
            ];

            if ($esAdmin) {
                $datos['total_usuarios'] = Usuario::count();
                $datos['top_contenido'] = RegistroVista::with(['usuario', 'redSocial'])
                    ->where('estado', 'activo')
                    ->orderByDesc('vistas')
                    ->limit(5)
                    ->get()
                    ->map(function ($r) {
                        return api_formatear_registro($r);
                    });
                $datos['top_usuarios'] = Usuario::withCount('registros')
                    ->orderByDesc(RegistroVista::selectRaw('SUM(vistas)')->whereColumn('usuario_id', 'usuarios.id'))
                    ->limit(5)
                    ->get()
                    ->map(function ($u) {
                        return api_formatear_usuario($u);
                    });
            }

            $datos['resumen_mensual'] = $scope(RegistroVista::query())
                ->selectRaw('MONTH(fecha_registro) as mes, YEAR(fecha_registro) as anio, SUM(vistas) as total_vistas, SUM(likes) as total_likes, COUNT(*) as total_registros')
                ->groupBy('anio', 'mes')
                ->orderBy('anio')
                ->orderBy('mes')
                ->limit(12)
                ->get()
                ->map(function ($f) {
                    return [
                        'mes' => (int) $f->mes,
                        'anio' => (int) $f->anio,
                        'total_vistas' => (int) $f->total_vistas,
                        'total_likes' => (int) $f->total_likes,
                        'total_registros' => (int) $f->total_registros,
                    ];
                });

            $datos['notificaciones_no_leidas'] = Notificacion::where('usuario_id', $usuario->id)->where('leida', false)->count();
            $datos['notificaciones_recientes'] = Notificacion::where('usuario_id', $usuario->id)
                ->latest('created_at')
                ->limit(5)
                ->get()
                ->map(function ($n) {
                    return api_formatear_notificacion($n);
                });

            return api_json($datos);
        });

        // ---------- Registros de vistas ----------
        Route::get('/registros', function (Request $request) {
            $usuario = api_usuario($request);

            $consulta = RegistroVista::with(['usuario', 'redSocial']);
            if (!$usuario->isAdmin()) {
                $consulta->where('usuario_id', $usuario->id);
            }

            if ($request->filled('buscar')) {
                $buscar = $request->buscar;
                $consulta->where(function ($q) use ($buscar) {
                    $q->where('titulo', 'like', "%{$buscar}%")
                        ->orWhere('descripcion', 'like', "%{$buscar}%");
                });
            }

            foreach (['red_social_id', 'estado', 'tipo_contenido'] as $filtro) {
                if ($request->filled($filtro)) {
                    $consulta->where($filtro, $request->$filtro);
                }
            }

            if ($request->filled('fecha_desde')) {
                $consulta->where('fecha_registro', '>=', $request->fecha_desde);
            }
            if ($request->filled('fecha_hasta')) {
                $consulta->where('fecha_registro', '<=', $request->fecha_hasta);
            }

            $porPagina = max(1, min(100, $request->integer('por_pagina', 12)));
            $paginador = $consulta->latest('fecha_registro')->paginate($porPagina)->withQueryString();

            return api_json([
                'registros' => collect($paginador->items())->map(function ($r) {
                    return api_formatear_registro($r);
                }),
                'paginacion' => api_paginacion($paginador),
            ]);
        });

        Route::post('/registros', function (Request $request) {
            $usuario = api_usuario($request);

            $error = api_validar($request, [
                'red_social_id' => 'required|exists:redes_sociales,id',
                'titulo' => 'required|string|max:200',
                'descripcion' => 'nullable|string',
                'vistas' => 'required|integer|min:0',
                'likes' => 'required|integer|min:0',
                'comentarios' => 'required|integer|min:0',
                'compartidos' => 'required|integer|min:0',
                'estado' => 'required|in:activo,inactivo,borrador',
                'tipo_contenido' => 'required|in:imagen,video,texto,stories,reel,live,podcast',
                'url_contenido' => 'nullable|url|max:500',
                'fecha_registro' => 'required|date',
            ]);
            if ($error) {
                return $error;
            }

            $registro = RegistroVista::create([
                'usuario_id' => $usuario->id,
                'red_social_id' => $request->red_social_id,
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'vistas' => $request->vistas,
                'likes' => $request->likes,
                'comentarios' => $request->comentarios,
                'compartidos' => $request->compartidos,
                'estado' => $request->estado,
                'tipo_contenido' => $request->tipo_contenido,
                'url_contenido' => $request->url_contenido,
                'fecha_registro' => $request->fecha_registro,
            ]);

            if ($usuario->isAdmin()) {
                $notificacion = [
                    'usuario_id' => $usuario->id,
                    'titulo' => 'Nuevo registro creado',
                    'mensaje' => "Se ha registrado el contenido \"{$registro->titulo}\" exitosamente.",
                    'leida' => false,
                ];
                if (api_tabla_tiene_columna('notificaciones', 'tipo')) {
                    $notificacion['tipo'] = 'exito';
                }
                Notificacion::create($notificacion);
            }

            return api_json(
                api_formatear_registro($registro->load(['usuario', 'redSocial'])),
                201,
                'Registro creado exitosamente.'
            );
        });

        Route::get('/registros/{id}', function (Request $request, $id) {
            $registro = api_buscar_registro($request, $id);
            if ($registro instanceof JsonResponse) {
                return $registro;
            }
            return api_json(api_formatear_registro($registro));
        });

        Route::put('/registros/{id}', function (Request $request, $id) {
            return api_actualizar_registro($request, $id);
        });

        Route::patch('/registros/{id}', function (Request $request, $id) {
            return api_actualizar_registro($request, $id);
        });

        Route::delete('/registros/{id}', function (Request $request, $id) {
            $registro = api_buscar_registro($request, $id);
            if ($registro instanceof JsonResponse) {
                return $registro;
            }
            $titulo = $registro->titulo;
            $registro->delete();
            return api_json(['id' => (int) $id, 'titulo' => $titulo], 200, 'Registro eliminado exitosamente.');
        });

        // ---------- Redes sociales ----------
        Route::get('/redes-sociales-activas', function () {
            $redes = RedSocial::where('activa', true)->latest()->get();
            return api_json($redes->map(function ($r) {
                return api_formatear_red($r);
            }));
        });

        Route::get('/redes-sociales', function () {
            $admin = api_requiere_admin(request());
            if ($admin instanceof JsonResponse) {
                return $admin;
            }
            $redes = RedSocial::withCount('registros')->latest()->get();
            return api_json($redes->map(function ($r) {
                return api_formatear_red($r);
            }));
        });

        Route::post('/redes-sociales', function (Request $request) {
            $admin = api_requiere_admin($request);
            if ($admin instanceof JsonResponse) {
                return $admin;
            }

            $error = api_validar($request, [
                'nombre' => 'required|string|max:100|unique:redes_sociales,nombre',
                'icono' => 'required|string|max:50',
                'color' => 'required|string|max:20',
                'url_logo' => 'nullable|url|max:500',
            ]);
            if ($error) {
                return $error;
            }

            $red = RedSocial::create($request->only(['nombre', 'icono', 'color', 'url_logo']));
            return api_json(api_formatear_red($red), 201, 'Red social creada exitosamente.');
        });

        Route::get('/redes-sociales/{id}', function (Request $request, $id) {
            $admin = api_requiere_admin($request);
            if ($admin instanceof JsonResponse) {
                return $admin;
            }
            $red = RedSocial::find($id);
            if (!$red) {
                return api_error('Red social no encontrada.', 404);
            }
            return api_json(api_formatear_red($red));
        });

        Route::put('/redes-sociales/{id}', function (Request $request, $id) {
            return api_actualizar_red($request, $id);
        });

        Route::patch('/redes-sociales/{id}', function (Request $request, $id) {
            return api_actualizar_red($request, $id);
        });

        Route::delete('/redes-sociales/{id}', function (Request $request, $id) {
            $admin = api_requiere_admin($request);
            if ($admin instanceof JsonResponse) {
                return $admin;
            }
            $red = RedSocial::find($id);
            if (!$red) {
                return api_error('Red social no encontrada.', 404);
            }
            $red->delete();
            return api_json(['id' => (int) $id], 200, 'Red social eliminada exitosamente.');
        });

        // ---------- Usuarios (admin) ----------
        Route::get('/usuarios', function (Request $request) {
            $admin = api_requiere_admin($request);
            if ($admin instanceof JsonResponse) {
                return $admin;
            }

            $consulta = Usuario::withCount('registros');

            if ($request->filled('buscar')) {
                $buscar = $request->buscar;
                $consulta->where(function ($q) use ($buscar) {
                    $q->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%");
                });
            }
            if ($request->filled('rol')) {
                $consulta->where('rol', $request->rol);
            }
            if ($request->filled('activo')) {
                $consulta->where('activo', $request->boolean('activo'));
            }

            $paginador = $consulta->latest('created_at')->paginate(10)->withQueryString();

            return api_json([
                'usuarios' => collect($paginador->items())->map(function ($u) {
                    return api_formatear_usuario($u);
                }),
                'paginacion' => api_paginacion($paginador),
            ]);
        });

        Route::post('/usuarios', function (Request $request) {
            $admin = api_requiere_admin($request);
            if ($admin instanceof JsonResponse) {
                return $admin;
            }

            $error = api_validar($request, [
                'nombre' => 'required|string|max:100',
                'email' => 'required|email|max:150|unique:usuarios,email',
                'password' => 'required|string|min:6',
                'password_confirmation' => 'required|string|same:password',
                'rol' => 'required|in:admin,cliente,usuario',
                'telefono' => 'nullable|string|max:20',
            ]);
            if ($error) {
                return $error;
            }

            $usuario = Usuario::create([
                'nombre' => $request->nombre,
                'email' => $request->email,
                'password' => $request->password,
                'rol' => $request->rol,
                'telefono' => $request->telefono,
                'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($request->nombre) . '&background=6366f1&color=fff&size=128',
            ]);

            return api_json(api_formatear_usuario($usuario), 201, 'Usuario creado exitosamente.');
        });

        Route::get('/usuarios/{id}', function (Request $request, $id) {
            $admin = api_requiere_admin($request);
            if ($admin instanceof JsonResponse) {
                return $admin;
            }
            $usuario = Usuario::withCount('registros')->find($id);
            if (!$usuario) {
                return api_error('Usuario no encontrado.', 404);
            }
            return api_json(api_formatear_usuario($usuario));
        });

        Route::put('/usuarios/{id}', function (Request $request, $id) {
            return api_actualizar_usuario($request, $id);
        });

        Route::patch('/usuarios/{id}', function (Request $request, $id) {
            return api_actualizar_usuario($request, $id);
        });

        Route::delete('/usuarios/{id}', function (Request $request, $id) {
            $admin = api_requiere_admin($request);
            if ($admin instanceof JsonResponse) {
                return $admin;
            }

            $usuario = Usuario::find($id);
            if (!$usuario) {
                return api_error('Usuario no encontrado.', 404);
            }
            if ($usuario->id === $admin->id) {
                return api_error('No puedes eliminar tu propia cuenta.', 409);
            }

            DB::table('api_tokens')->where('usuario_id', $usuario->id)->delete();
            $usuario->delete();

            return api_json(['id' => (int) $id], 200, 'Usuario eliminado exitosamente.');
        });

        Route::post('/usuarios/{id}/toggle', function (Request $request, $id) {
            $admin = api_requiere_admin($request);
            if ($admin instanceof JsonResponse) {
                return $admin;
            }

            $usuario = Usuario::find($id);
            if (!$usuario) {
                return api_error('Usuario no encontrado.', 404);
            }
            if ($usuario->id === $admin->id) {
                return api_error('No puedes desactivar tu propia cuenta.', 409);
            }

            $usuario->update(['activo' => !$usuario->activo]);

            return api_json(
                api_formatear_usuario($usuario),
                200,
                'Usuario ' . ($usuario->activo ? 'activado' : 'desactivado') . ' exitosamente.'
            );
        });

        // ---------- Notificaciones ----------
        Route::get('/notificaciones', function (Request $request) {
            $usuario = api_usuario($request);
            $paginador = Notificacion::where('usuario_id', $usuario->id)
                ->latest('created_at')
                ->paginate(20)
                ->withQueryString();

            return api_json([
                'notificaciones' => collect($paginador->items())->map(function ($n) {
                    return api_formatear_notificacion($n);
                }),
                'paginacion' => api_paginacion($paginador),
            ]);
        });

        Route::get('/notificaciones/contador', function (Request $request) {
            $usuario = api_usuario($request);
            $total = Notificacion::where('usuario_id', $usuario->id)->where('leida', false)->count();
            return api_json(['no_leidas' => $total]);
        });

        Route::post('/notificaciones/{id}/leer', function (Request $request, $id) {
            $usuario = api_usuario($request);
            $notificacion = Notificacion::where('usuario_id', $usuario->id)->find($id);
            if (!$notificacion) {
                return api_error('Notificación no encontrada.', 404);
            }
            $notificacion->update(['leida' => true]);
            return api_json(api_formatear_notificacion($notificacion), 200, 'Notificación marcada como leída.');
        });

        Route::post('/notificaciones/marcar-todas-leidas', function (Request $request) {
            $usuario = api_usuario($request);
            Notificacion::where('usuario_id', $usuario->id)
                ->where('leida', false)
                ->update(['leida' => true]);
            return api_json(null, 200, 'Todas las notificaciones fueron marcadas como leídas.');
        });

        // ---------- Reportes ----------
        Route::get('/reportes/parametros', function (Request $request) {
            $usuario = api_usuario($request);
            $redes = RedSocial::where('activa', true)->get()->map(function ($r) {
                return api_formatear_red($r);
            });
            $usuarios = $usuario->isAdmin()
                ? Usuario::where('activo', true)->get()->map(function ($u) {
                    return api_formatear_usuario($u);
                })
                : collect();

            return api_json([
                'redes_sociales' => $redes,
                'usuarios' => $usuarios,
            ]);
        });

        $construirReporte = function (Request $request) {
            $usuario = api_usuario($request);
            $consulta = RegistroVista::with(['usuario', 'redSocial']);

            if (!$usuario->isAdmin()) {
                $consulta->where('usuario_id', $usuario->id);
            }

            $consulta->whereBetween('fecha_registro', [$request->fecha_desde, $request->fecha_hasta]);

            if ($request->filled('red_social_id')) {
                $consulta->where('red_social_id', $request->red_social_id);
            }
            if ($request->filled('usuario_filtro') && $usuario->isAdmin()) {
                $consulta->where('usuario_id', $request->usuario_filtro);
            }
            if ($request->filled('estado')) {
                $consulta->where('estado', $request->estado);
            }
            if ($request->filled('tipo_contenido')) {
                $consulta->where('tipo_contenido', $request->tipo_contenido);
            }

            return $consulta->orderBy('fecha_registro', 'desc')->get();
        };

        Route::post('/reportes/generar', function (Request $request) use ($construirReporte) {
            $error = api_validar($request, [
                'fecha_desde' => 'required|date',
                'fecha_hasta' => 'required|date|after_or_equal:fecha_desde',
            ]);
            if ($error) {
                return $error;
            }

            $usuario = api_usuario($request);
            $registros = $construirReporte($request);

            // Formatear fecha manualmente para el agrupado por día
            $registros->each(function ($r) {
                $r->setAttribute('_fecha_key', $r->fecha_registro ? $r->fecha_registro->format('Y-m-d') : '');
            });

            $resumen = [
                'total_registros' => $registros->count(),
                'total_vistas' => $registros->sum('vistas'),
                'total_likes' => $registros->sum('likes'),
                'total_comentarios' => $registros->sum('comentarios'),
                'total_compartidos' => $registros->sum('compartidos'),
                'promedio_vistas' => $registros->count() > 0 ? (int) round($registros->sum('vistas') / $registros->count()) : 0,
                'mejor_registro' => $registros->sortByDesc('vistas')->first() ? api_formatear_registro($registros->sortByDesc('vistas')->first()) : null,
                'por_red' => $registros->groupBy('red_social_id')->map(function ($grupo) {
                    return [
                        'id' => $grupo->first()->red_social_id,
                        'nombre' => $grupo->first()->redSocial->nombre ?? 'N/A',
                        'color' => $grupo->first()->redSocial->color ?? '#6366f1',
                        'total' => $grupo->count(),
                        'vistas' => $grupo->sum('vistas'),
                        'likes' => $grupo->sum('likes'),
                    ];
                })->values(),
                'por_tipo' => $registros->groupBy('tipo_contenido')->map(function ($grupo) {
                    return [
                        'tipo' => $grupo->first()->tipo_contenido,
                        'total' => $grupo->count(),
                        'vistas' => $grupo->sum('vistas'),
                    ];
                })->values(),
                'por_estado' => $registros->groupBy('estado')->map(function ($grupo) {
                    return [
                        'estado' => $grupo->first()->estado,
                        'total' => $grupo->count(),
                    ];
                })->values(),
                'por_dia' => $registros->groupBy('_fecha_key')->map(function ($grupo) {
                    return [
                        'fecha' => $grupo->first()->_fecha_key,
                        'vistas' => $grupo->sum('vistas'),
                        'likes' => $grupo->sum('likes'),
                        'registros' => $grupo->count(),
                    ];
                })->sortBy('fecha')->values(),
                'por_usuario' => $usuario->isAdmin()
                    ? $registros->groupBy('usuario_id')->map(function ($grupo) {
                        return [
                            'id' => $grupo->first()->usuario_id,
                            'nombre' => $grupo->first()->usuario->nombre ?? 'N/A',
                            'total' => $grupo->count(),
                            'vistas' => $grupo->sum('vistas'),
                        ];
                    })->values()
                    : collect(),
            ];

            return api_json([
                'resumen' => $resumen,
                'registros' => $registros->map(function ($r) {
                    return api_formatear_registro($r);
                }),
            ]);
        });

        Route::get('/reportes/exportar/{formato}', function (Request $request, string $formato) use ($construirReporte) {
            $formato = strtolower($formato);

            $error = api_validar($request, [
                'fecha_desde' => 'required|date',
                'fecha_hasta' => 'required|date|after_or_equal:fecha_desde',
            ]);
            if ($error) {
                return $error;
            }

            if (!in_array($formato, ['csv', 'excel', 'xlsx', 'pdf'], true)) {
                return api_error('Formato de exportación no soportado.', 400);
            }

            if ($formato === 'csv') {
                $registros = $construirReporte($request);
                $csv = api_generar_csv($registros);
                return response($csv, 200, [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => 'attachment; filename="reporte.csv"',
                ]);
            }

            return api_error(
                "La exportación en formato {$formato} estará disponible próximamente.",
                501
            );
        });
    });
});

// ============================================================
// Helpers de rutas compartidas (definidos al final del archivo)
// ============================================================
if (!function_exists('api_actualizar_registro')) {
    function api_actualizar_registro(Request $request, $id)
    {
        $registro = api_buscar_registro($request, $id);
        if ($registro instanceof JsonResponse) {
            return $registro;
        }

        $error = api_validar($request, [
            'red_social_id' => 'required|exists:redes_sociales,id',
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'vistas' => 'required|integer|min:0',
            'likes' => 'required|integer|min:0',
            'comentarios' => 'required|integer|min:0',
            'compartidos' => 'required|integer|min:0',
            'estado' => 'required|in:activo,inactivo,borrador',
            'tipo_contenido' => 'required|in:imagen,video,texto,stories,reel,live,podcast',
            'url_contenido' => 'nullable|url|max:500',
            'fecha_registro' => 'required|date',
        ]);
        if ($error) {
            return $error;
        }

        $registro->update($request->only([
            'red_social_id', 'titulo', 'descripcion', 'vistas', 'likes',
            'comentarios', 'compartidos', 'estado', 'tipo_contenido',
            'url_contenido', 'fecha_registro',
        ]));

        return api_json(
            api_formatear_registro($registro->load(['usuario', 'redSocial'])),
            200,
            'Registro actualizado exitosamente.'
        );
    }
}

if (!function_exists('api_actualizar_red')) {
    function api_actualizar_red(Request $request, $id)
    {
        $admin = api_requiere_admin($request);
        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        $red = RedSocial::find($id);
        if (!$red) {
            return api_error('Red social no encontrada.', 404);
        }

        $error = api_validar($request, [
            'nombre' => 'required|string|max:100|unique:redes_sociales,nombre,' . $id,
            'icono' => 'required|string|max:50',
            'color' => 'required|string|max:20',
            'url_logo' => 'nullable|url|max:500',
            'activa' => 'required|boolean',
        ]);
        if ($error) {
            return $error;
        }

        $red->update($request->only(['nombre', 'icono', 'color', 'url_logo', 'activa']));

        return api_json(api_formatear_red($red), 200, 'Red social actualizada exitosamente.');
    }
}

if (!function_exists('api_actualizar_usuario')) {
    function api_actualizar_usuario(Request $request, $id)
    {
        $admin = api_requiere_admin($request);
        if ($admin instanceof JsonResponse) {
            return $admin;
        }

        $usuario = Usuario::find($id);
        if (!$usuario) {
            return api_error('Usuario no encontrado.', 404);
        }

        $reglas = [
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:usuarios,email,' . $id,
            'rol' => 'required|in:admin,cliente,usuario',
            'telefono' => 'nullable|string|max:20',
            'activo' => 'required|boolean',
            'password' => 'nullable|string|min:6',
        ];

        $error = api_validar($request, $reglas);
        if ($error) {
            return $error;
        }

        $datos = $request->only(['nombre', 'email', 'rol', 'telefono', 'activo']);

        if ($request->filled('password')) {
            $datos['password'] = $request->password;
        }

        $usuario->update($datos);

        return api_json(api_formatear_usuario($usuario->loadCount('registros')), 200, 'Usuario actualizado exitosamente.');
    }
}