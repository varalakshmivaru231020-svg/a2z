<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            // Applications outlive the posting: deleting a job keeps the candidate record.
            $table->foreignId('job_opening_id')->nullable()->constrained()->nullOnDelete();
            $table->string('job_title');
            $table->string('name');
            $table->string('email');
            $table->string('phone', 30);
            $table->string('current_location', 120)->nullable();
            $table->string('experience', 60)->nullable();
            $table->text('cover_note')->nullable();
            $table->string('resume_path');
            $table->string('resume_name');
            $table->string('status', 20)->default('new')->index(); // new|reviewed|shortlisted|rejected|hired
            $table->text('admin_notes')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->index(['job_opening_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
