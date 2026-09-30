<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('makosa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('kosa');
            $table->timestamps();

            $table->unique(['company_id', 'kosa']);
        });

        Schema::create('mlipuzi_kosa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mlipuzi_id')->constrained('walipuaji')->cascadeOnDelete();
            $table->foreignId('kosa_id')->constrained('makosa')->restrictOnDelete();
            $table->text('maelezo_ya_adhabu')->nullable();
            $table->string('barua')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mlipuzi_kosa');
        Schema::dropIfExists('makosa');
    }
};
