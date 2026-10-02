<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo_edits', function (Blueprint $table): void {
            $table->id();
            $table->string('source_path');
            $table->string('reference_path')->nullable();
            $table->text('prompt');
            $table->string('status')->default('queued');
            $table->string('result_path')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photo_edits');
    }
};
