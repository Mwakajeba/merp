<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vibali_vya_mawe', function (Blueprint $table) {
            $table->string('aina_ya_mzigo', 20)->default('mawe')->after('idadi_ya_mifuko');
        });
    }

    public function down(): void
    {
        Schema::table('vibali_vya_mawe', function (Blueprint $table) {
            $table->dropColumn('aina_ya_mzigo');
        });
    }
};
