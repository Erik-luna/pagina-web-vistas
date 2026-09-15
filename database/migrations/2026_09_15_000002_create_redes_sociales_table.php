<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redes_sociales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('icono', 50);
            $table->string('color', 20);
            $table->string('url_logo')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redes_sociales');
    }
};
