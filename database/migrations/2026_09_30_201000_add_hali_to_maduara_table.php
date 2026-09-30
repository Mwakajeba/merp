<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maduara', function (Blueprint $table) {
            $table->enum('hali', ['inafanya_kazi', 'imefungwa'])->default('inafanya_kazi')->after('maelezo');
        });
    }

    public function down(): void
    {
        Schema::table('maduara', function (Blueprint $table) {
            $table->dropColumn('hali');
        });
    }
};
