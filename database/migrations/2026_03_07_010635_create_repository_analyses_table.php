<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repository_analyses', function (Blueprint $table) {
            $table->id();
            $table->string('repository_url');
            $table->string('repository_name')->nullable();
            $table->string('owner')->nullable();
            $table->json('metrics')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repository_analyses');
    }
};
