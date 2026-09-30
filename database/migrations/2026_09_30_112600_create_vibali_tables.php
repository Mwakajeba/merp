<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vibali', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('namba', 20);
            $table->foreignId('duara_id')->constrained('maduara')->restrictOnDelete();
            $table->unsignedInteger('idadi_ya_matundu');
            $table->string('bc_no')->nullable();
            $table->date('tarehe');
            $table->foreignId('msimamizi_id')->constrained('wasimamizi')->restrictOnDelete();
            $table->foreignId('mlipuzi_id')->constrained('walipuaji')->restrictOnDelete();
            $table->decimal('cotex', 10, 2)->nullable();
            $table->decimal('dull_fuse', 10, 2)->nullable();
            $table->string('msimamizi_wa_idara');
            $table->string('katibu');
            $table->enum('hali', ['uzalishaji', 'ufreshiaji', 'ufukuziaji']);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'namba']);
        });

        Schema::create('kibali_mchorongaji', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kibali_id')->constrained('vibali')->cascadeOnDelete();
            $table->foreignId('mlipuzi_id')->constrained('walipuaji')->restrictOnDelete();
            $table->unsignedTinyInteger('nafasi');
            $table->timestamps();

            $table->unique(['kibali_id', 'nafasi']);
            $table->unique(['kibali_id', 'mlipuzi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kibali_mchorongaji');
        Schema::dropIfExists('vibali');
    }
};
