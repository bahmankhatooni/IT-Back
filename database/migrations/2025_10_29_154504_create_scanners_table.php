<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scanners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('cities')->onDelete('cascade'); // شهرستان
            $table->string('model');     // مدل اسکنر
            $table->integer('quantity'); // تعداد
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scanners');
    }
};
