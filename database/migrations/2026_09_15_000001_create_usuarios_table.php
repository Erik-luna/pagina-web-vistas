<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->enum('rol', ['admin', 'cliente'])->default('cliente');
            $table->string('avatar')->nullable()->default('https://ui-avatars.com/api/?name=U&background=6366f1&color=fff&size=128');
            $table->string('telefono', 20)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
