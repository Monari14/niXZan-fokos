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
        Schema::create('followers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_seguidor');
            $table->unsignedBigInteger('id_seguindo');
            $table->timestamps();

            $table->foreign('id_seguidor')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_seguindo')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['id_seguidor', 'id_seguindo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('followers');
    }
};
