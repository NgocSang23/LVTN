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
        Schema::create('test__multiple_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('test_id');
            $table->unsignedInteger('multiplequestion_id');
            $table->foreign('test_id')->references('id')->on('tests');
            $table->foreign('multiplequestion_id')->references('id')->on('multiple_questions');
            $table->unique([
                'test_id',
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
        Schema::dropIfExists('test__multiple_questions');
    }
};
