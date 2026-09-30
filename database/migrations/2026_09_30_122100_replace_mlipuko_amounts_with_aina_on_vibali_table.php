<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vibali', function (Blueprint $table) {
            $table->enum('aina_ya_mlipuko', ['cotex', 'dull_fuse'])->nullable()->after('mlipuzi_id');
        });

        DB::table('vibali')->whereNotNull('cotex')->whereNull('aina_ya_mlipuko')->update([
            'aina_ya_mlipuko' => 'cotex',
        ]);
        DB::table('vibali')->whereNotNull('dull_fuse')->whereNull('aina_ya_mlipuko')->update([
            'aina_ya_mlipuko' => 'dull_fuse',
        ]);

        Schema::table('vibali', function (Blueprint $table) {
            $table->dropColumn(['cotex', 'dull_fuse']);
        });
    }

    public function down(): void
    {
        Schema::table('vibali', function (Blueprint $table) {
            $table->decimal('cotex', 10, 2)->nullable()->after('mlipuzi_id');
            $table->decimal('dull_fuse', 10, 2)->nullable()->after('cotex');
        });

        DB::table('vibali')->where('aina_ya_mlipuko', 'cotex')->update(['cotex' => 1]);
        DB::table('vibali')->where('aina_ya_mlipuko', 'dull_fuse')->update(['dull_fuse' => 1]);

        Schema::table('vibali', function (Blueprint $table) {
            $table->dropColumn('aina_ya_mlipuko');
        });
    }
};
