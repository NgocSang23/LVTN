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
        Schema::create('question_numbers', function (Blueprint $table) {
            $table->integer('question_number');
            $table->unsignedInteger('test_id');
            $table->unsignedInteger('topic_id');
            $table->foreign('test_id')->references('id')->on('tests');
            $table->foreign('topic_id')->references('id')->on('topics');
            $table->primary([
                'test_id',
                'topic_id'
            ]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_numbers');
    }
};
