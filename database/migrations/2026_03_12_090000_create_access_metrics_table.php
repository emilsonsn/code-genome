<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('metric_date');
            $table->string('route_name')->nullable();
            $table->string('path');
            $table->string('method', 10);
            $table->string('ip_address', 45);
            $table->unsignedBigInteger('hits')->default(0);
            $table->timestamps();

            $table->unique(['metric_date', 'route_name', 'path', 'method', 'ip_address'], 'access_metrics_unique_key');
            $table->index(['metric_date', 'hits']);
            $table->index(['metric_date', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_metrics');
    }
};
