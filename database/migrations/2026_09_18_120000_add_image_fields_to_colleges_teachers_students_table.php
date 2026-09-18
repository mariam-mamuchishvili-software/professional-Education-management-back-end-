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
        Schema::table('colleges', function (Blueprint $table) {
            $table->string('poster')->nullable()->after('website');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->string('image')->nullable()->after('specialization');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('image')->nullable()->after('birth_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('colleges', function (Blueprint $table) {
            $table->dropColumn('poster');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('image');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
