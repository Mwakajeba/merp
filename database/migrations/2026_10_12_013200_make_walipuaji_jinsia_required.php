<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('walipuaji')->whereNull('jinsia')->update(['jinsia' => 'me']);
        DB::statement("ALTER TABLE walipuaji MODIFY jinsia ENUM('me', 'ke') NOT NULL DEFAULT 'me'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE walipuaji MODIFY jinsia ENUM('me', 'ke') NULL DEFAULT NULL");
    }
};
