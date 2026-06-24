<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('bank_accounts', 'swift_code')) {
                $table->string('swift_code', 50)->nullable()->after('account_number');
            }
            if (!Schema::hasColumn('bank_accounts', 'bank_branch_name')) {
                $table->string('bank_branch_name', 255)->nullable()->after('swift_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('bank_accounts', 'bank_branch_name')) {
                $table->dropColumn('bank_branch_name');
            }
            if (Schema::hasColumn('bank_accounts', 'swift_code')) {
                $table->dropColumn('swift_code');
            }
        });
    }
};
