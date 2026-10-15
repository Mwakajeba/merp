<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kompresa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('jina');
            $table->string('mkoa');
            $table->string('wilaya');
            $table->string('mtaa')->nullable();
            $table->text('maelezo')->nullable();
            $table->string('simu', 30);
            $table->string('simu_mbadala', 30)->nullable();
            $table->string('namba');
            $table->string('picha')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'namba']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kompresa');
    }
};
