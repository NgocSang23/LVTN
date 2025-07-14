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
        Schema::table('classroom_tests', function (Blueprint $table) {
            $table->timestamp('deadline')->nullable()->after('test_id'); // 🕒 thêm deadline
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classroom_tests', function (Blueprint $table) {
            $table->dropColumn('deadline'); // ⏪ xóa khi rollback
        });
    }
};
