<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('computers', function (Blueprint $table) {
            $table->id();
            $table->string('name');                     // نام کامپیوتر
            $table->foreignId('city_id')->constrained('cities')->onDelete('cascade'); // شهرستان
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade'); // شعبه
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade'); // کارمند

            // مشخصات سخت افزاری
            $table->string('mb')->nullable();  // MB
            $table->string('cpu')->nullable();
            $table->string('ram')->nullable();
            $table->string('hard')->nullable();
            $table->string('os')->nullable();

            $table->boolean('antivirus')->default(false); // آیا آنتی ویروس دارد؟
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('computers');
    }
};
