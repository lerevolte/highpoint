<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('channel_group_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('channel_group_id')->constrained()->onDelete('cascade');
            $table->string('source');
            $table->string('medium');
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('channel_group_rules'); }
};
