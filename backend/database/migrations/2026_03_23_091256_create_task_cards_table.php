<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('column_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->string('category')->nullable();
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->date('due_date')->nullable();
            $table->json('attachments')->nullable(); // array of file paths/urls
            $table->json('labels')->nullable(); // array of label strings
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_cards');
    }
};
