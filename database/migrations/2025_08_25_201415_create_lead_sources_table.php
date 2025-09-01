<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('lead_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('source');
            $table->string('medium');
            $table->timestamps();
            $table->unique(['project_id', 'source', 'medium']);
        });
    }
    public function down() { Schema::dropIfExists('lead_sources'); }
};
