<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_openings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('department', 80)->nullable();
            $table->string('location', 120);
            $table->string('employment_type', 30)->default('full_time');
            $table->string('experience', 80)->nullable();
            $table->string('salary', 80)->nullable();
            $table->unsignedSmallInteger('vacancies')->default(1);
            $table->string('summary', 300);
            $table->longText('description');
            $table->longText('responsibilities')->nullable();
            $table->longText('requirements')->nullable();
            $table->date('closing_date')->nullable();
            $table->string('status', 10)->default('open')->index(); // open | closed
            $table->timestamp('published_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_openings');
    }
};
