<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('influencers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type'); // youtuber, streamer, blogger, instagram, tiktok, etc
            $table->string('channel_url')->nullable();
            $table->string('email')->nullable();
            $table->string('instagram')->nullable();
            $table->string('twitter')->nullable();
            $table->string('discord')->nullable();
            $table->string('country', 100)->default('Brazil');
            $table->string('language', 50)->default('Portuguese');
            $table->string('niche')->nullable(); // game dev, indie, fps, rpg, etc
            $table->bigInteger('subscribers')->default(0);
            $table->string('status')->default('prospect'); // prospect, contacted, negotiating, active, inactive
            $table->text('notes')->nullable();
            $table->decimal('avg_views', 15, 2)->nullable();
            $table->decimal('engagement_rate', 5, 2)->nullable();
            $table->date('last_contact_date')->nullable();
            $table->json('games_covered')->nullable(); // array of game names
            $table->json('tags')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('influencers');
    }
};
