<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vibali_vya_mawe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('namba', 20);
            $table->foreignId('duara_id')->constrained('maduara')->restrictOnDelete();
            $table->unsignedInteger('idadi_ya_mifuko');
            $table->date('tarehe');
            $table->foreignId('msimamizi_id')->constrained('wasimamizi')->restrictOnDelete();
            $table->string('katibu');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'namba']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vibali_vya_mawe');
    }
};
