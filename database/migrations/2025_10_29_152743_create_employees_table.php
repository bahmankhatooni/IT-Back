<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();       // کد پرسنلی
            $table->string('fname');                // نام
            $table->string('lname');                // نام خانوادگی
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade'); // شعبه
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
