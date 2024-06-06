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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('youtube_id')->unique();
            $table->integer('like_count')->default(0);
            $table->integer('likes')->default(0);
            $table->integer('dislikes')->default(0);
            $table->timestamp('published_at');
            $table->boolean('watched')->default(false);
            $table->integer('rating')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
