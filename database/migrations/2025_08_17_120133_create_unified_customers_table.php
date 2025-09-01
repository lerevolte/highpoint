<?php
// Файл: database/migrations/YYYY_MM_DD_HHMMSS_create_unified_customers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('unified_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('crm_user_id')->nullable()->index(); // ID из CRM клиента
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('unified_customers');
    }
};