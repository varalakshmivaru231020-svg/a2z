<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('key', 40)->unique();
            $table->string('name', 80);
            $table->string('blurb', 300)->nullable();
            $table->string('icon', 40)->default('briefcase');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $order = 10;
        foreach (config('site.service_categories', []) as $key => $cat) {
            DB::table('service_categories')->insert([
                'key' => $key,
                'name' => $cat['name'],
                'blurb' => $cat['blurb'] ?? null,
                'icon' => $cat['icon'] ?? 'briefcase',
                'sort_order' => $order,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $order += 10;
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_categories');
    }
};
