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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Título a mostrar (ej: 'Dashboard', 'Clientes')
            $table->string('href');  // La ruta (ej: '/dashboard', '/clients', o un nombre de ruta 'dashboard')
            $table->string('icon')->nullable(); // Nombre del icono (ej: 'LayoutGrid', 'PersonStanding')
            $table->boolean('status')->default(true); // Si está activo o no
            $table->integer('order')->default(0); // Orden para mostrar en el menú
            $table->boolean('is_admin_only')->default(false); // Si solo es visible para admins
            // $table->unsignedBigInteger('parent_id')->nullable(); // Opcional: para submenús
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
