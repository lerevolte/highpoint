<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained();
            $table->string('tracker_domain'); // Домен, где установлен счетчик
            $table->string('url');
            $table->string('referrer')->nullable();
            $table->string('ip_address');
            $table->string('user_agent');
            $table->integer('screen_width')->nullable();
            $table->integer('screen_height')->nullable();
            $table->string('language')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('session_id');
            $table->boolean('is_new_session')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
