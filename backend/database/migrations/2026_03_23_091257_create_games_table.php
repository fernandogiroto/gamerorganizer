<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('genre')->nullable();
            $table->string('engine')->nullable(); // Unity, Unreal, Godot, etc
            $table->string('status')->default('development'); // development, beta, released, abandoned
            $table->date('release_date')->nullable();
            // Platform links
            $table->string('steam_url')->nullable();
            $table->string('steam_app_id')->nullable();
            $table->string('itch_url')->nullable();
            $table->string('epic_url')->nullable();
            $table->string('gog_url')->nullable();
            $table->string('android_url')->nullable();
            $table->string('ios_url')->nullable();
            $table->string('website_url')->nullable();
            // Cached Steam data (refreshed periodically)
            $table->json('steam_data')->nullable();
            $table->timestamp('steam_data_updated_at')->nullable();
            $table->json('custom_links')->nullable(); // array of {label, url}
            $table->json('tags')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
