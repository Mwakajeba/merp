<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('walipuaji', function (Blueprint $table) {
            $table->string('namba_ya_usajili', 100)->nullable()->after('jina');
            $table->string('cheti_cha_milipuko')->nullable()->after('picha');
            $table->enum('jinsia', ['me', 'ke'])->nullable()->after('namba_ya_usajili');
            $table->date('tarehe_ya_kuzaliwa')->nullable()->after('jinsia');
            $table->unique(['company_id', 'namba_ya_usajili']);
        });
    }

    public function down(): void
    {
        Schema::table('walipuaji', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'namba_ya_usajili']);
            $table->dropColumn(['namba_ya_usajili', 'cheti_cha_milipuko', 'jinsia', 'tarehe_ya_kuzaliwa']);
        });
    }
};
