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
        Schema::create('test_results', function (Blueprint $table) {
            $table->text('answer');
            $table->unsignedInteger('option_id');
            $table->unsignedInteger('multiplequestion_id');
            $table->foreign('option_id')->references('id')->on('options');
            $table->foreign('multiplequestion_id')->references('id')->on('multiple_questions');
            $table->primary([
                'option_id',
                'multiplequestion_id'
            ]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_results');
    }
};
