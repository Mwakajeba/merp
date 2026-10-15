<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kompresa', function (Blueprint $table) {
            $table->string('uthibitisho_token', 64)->nullable()->unique()->after('created_by');
        });

        foreach (DB::table('kompresa')->whereNull('uthibitisho_token')->orderBy('id')->cursor() as $row) {
            DB::table('kompresa')->where('id', $row->id)->update([
                'uthibitisho_token' => Str::lower(Str::random(40)),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('kompresa', function (Blueprint $table) {
            $table->dropUnique(['uthibitisho_token']);
            $table->dropColumn('uthibitisho_token');
        });
    }
};
