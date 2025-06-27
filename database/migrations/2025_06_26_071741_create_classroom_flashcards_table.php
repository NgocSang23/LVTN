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
        Schema::create('classroom_flashcards', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('classroom_id');
            $table->unsignedInteger('flashcard_set_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('classroom_id')->references('id')->on('class_rooms');
            $table->foreign('flashcard_set_id')->references('id')->on('flashcard_sets');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classroom_flashcards');
    }
};
