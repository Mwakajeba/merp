<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maduara', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('namba');
            $table->text('maelezo')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'namba']);
        });

        Schema::create('wasimamizi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('jina');
            $table->string('simu');
            $table->timestamps();

            $table->unique(['company_id', 'simu']);
        });

        Schema::create('wanachama', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('jina');
            $table->string('simu');
            $table->timestamps();

            $table->unique(['company_id', 'simu']);
        });

        Schema::create('duara_msimamizi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('duara_id')->constrained('maduara')->cascadeOnDelete();
            $table->foreignId('msimamizi_id')->constrained('wasimamizi')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['duara_id', 'msimamizi_id']);
        });

        Schema::create('duara_mwanachama', function (Blueprint $table) {
            $table->id();
            $table->foreignId('duara_id')->constrained('maduara')->cascadeOnDelete();
            $table->foreignId('mwanachama_id')->constrained('wanachama')->cascadeOnDelete();
            $table->decimal('hisa', 12, 2)->nullable();
            $table->timestamps();

            $table->unique(['duara_id', 'mwanachama_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duara_mwanachama');
        Schema::dropIfExists('duara_msimamizi');
        Schema::dropIfExists('wanachama');
        Schema::dropIfExists('wasimamizi');
        Schema::dropIfExists('maduara');
    }
};
