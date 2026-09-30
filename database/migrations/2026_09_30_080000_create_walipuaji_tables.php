<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('walipuaji', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('jina');
            $table->string('bc_no');
            $table->string('simu');
            $table->string('simu_mbadala')->nullable();
            $table->string('picha')->nullable();
            $table->string('mkoa');
            $table->string('wilaya');
            $table->text('eneo')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'bc_no']);
        });

        Schema::create('mlipuzi_wawasiliani', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mlipuzi_id')->constrained('walipuaji')->cascadeOnDelete();
            $table->string('jina');
            $table->string('simu');
            $table->string('uhusiano');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mlipuzi_wawasiliani');
        Schema::dropIfExists('walipuaji');
    }
};
