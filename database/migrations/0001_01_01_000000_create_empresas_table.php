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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre de la empresa
            $table->string('ruc')->nullable(); // RUC o identificación fiscal (opcional)
            $table->string('address')->nullable(); // Dirección (opcional)
            $table->string('phone')->nullable(); // Teléfono (opcional)
            $table->string('email')->nullable(); // Email de contacto (opcional)
            $table->boolean('status')->default(true); // Estado (Activa/Inactiva)
            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};