<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuarios_libros', function (Blueprint $table) {
            $table->id();
            $table->integer('pagina_actual')->default(0);
            $table->decimal('porcentaje_leido', 5,2)->default(0);
            $table->enum('estado', ['pendiente', 'leyendo', 'terminado'])->default('pendiente');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedBigInteger('libro_id');
            $table->foreign('libro_id')->references('id')->on('libros');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios_libros');
    }
};
