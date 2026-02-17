<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kode_rekening', function (Blueprint $table) {
            $table->id();

            $table->string('kode', 50)->unique();
            $table->string('nama', 255);

            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('kode_rekening')
                  ->nullOnDelete();

            $table->unsignedTinyInteger('level'); // 1–6
            $table->enum('tipe', ['header','detail'])->default('header');
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['parent_id','level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kode_rekening');
    }
};
