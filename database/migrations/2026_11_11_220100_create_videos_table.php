<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            // Lien complet de la publication TikTok (https://www.tiktok.com/@compte/video/123...)
            $table->string('source_url');
            $table->string('tiktok_id')->unique();
            $table->string('type')->default('video'); // video ou photo
            $table->string('cover')->nullable();      // miniature, dans le disque public
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
