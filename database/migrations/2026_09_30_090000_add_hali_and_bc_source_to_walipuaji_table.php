<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('walipuaji', function (Blueprint $table) {
            $table->index('company_id', 'walipuaji_company_id_foreign');
            $table->dropUnique(['company_id', 'bc_no']);
        });

        DB::statement('ALTER TABLE walipuaji MODIFY bc_no VARCHAR(255) NULL');

        Schema::table('walipuaji', function (Blueprint $table) {
            $table->enum('hali', ['active', 'blocked'])->default('active')->after('jina');
            $table->enum('aina_ya_bc', ['yake', 'mtu'])->default('yake')->after('hali');
            $table->unsignedBigInteger('bc_ya_mlipuzi_id')->nullable()->after('bc_no');
            $table->foreign('bc_ya_mlipuzi_id')->references('id')->on('walipuaji')->restrictOnDelete();
            $table->unique(['company_id', 'bc_no']);
        });
    }

    public function down(): void
    {
        Schema::table('walipuaji', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'bc_no']);
            $table->dropForeign(['bc_ya_mlipuzi_id']);
            $table->dropColumn(['hali', 'aina_ya_bc', 'bc_ya_mlipuzi_id']);
        });

        DB::statement("UPDATE walipuaji SET bc_no = CONCAT('BC-', id) WHERE bc_no IS NULL");
        DB::statement('ALTER TABLE walipuaji MODIFY bc_no VARCHAR(255) NOT NULL');

        Schema::table('walipuaji', function (Blueprint $table) {
            $table->unique(['company_id', 'bc_no']);
        });
    }
};
