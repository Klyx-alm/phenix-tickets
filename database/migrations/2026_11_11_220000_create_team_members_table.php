<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role');
            $table->string('note')->nullable();
            // Chemin dans le disque public (ex. team/xxx.jpg) ou fichier du projet (images/team/xxx.jpg)
            $table->string('photo')->nullable();
            // Zone de la photo à garder visible dans le cadre (x% y%)
            $table->string('focus')->default('50% 25%');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
