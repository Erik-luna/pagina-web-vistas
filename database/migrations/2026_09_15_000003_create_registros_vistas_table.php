<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registros_vistas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('red_social_id')->constrained('redes_sociales')->onDelete('cascade');
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->bigInteger('vistas')->default(0);
            $table->bigInteger('likes')->default(0);
            $table->bigInteger('comentarios')->default(0);
            $table->bigInteger('compartidos')->default(0);
            $table->enum('estado', ['activo', 'inactivo', 'borrador'])->default('activo');
            $table->enum('tipo_contenido', ['imagen', 'video', 'texto', 'stories', 'reel', 'live', 'podcast'])->default('imagen');
            $table->string('url_contenido')->nullable();
            $table->date('fecha_registro');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_vistas');
    }
};
