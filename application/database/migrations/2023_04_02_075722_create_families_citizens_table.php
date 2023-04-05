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
        Schema::create('citizen_family', function (Blueprint $table) {
            $table->id();

            $table->foreignId('citizen_id')->constrained('citizens');
            $table->foreignId('family_id')->constrained('families');
            $table->string('role_id')->constrained('roles');
            $table->boolean('in_charge')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('families_citizens');
    }
};
