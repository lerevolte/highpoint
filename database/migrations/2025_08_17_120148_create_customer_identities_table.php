<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('customer_identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unified_customer_id')->constrained()->onDelete('cascade');
            $table->string('identity_type')->index(); // Например: 'user_id', 'metrika_client_id', 'session_id'
            $table->string('identity_value')->index();
            $table->timestamps();

            $table->unique(['identity_type', 'identity_value']); // Каждый идентификатор должен быть уникальным
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_identities');
    }
};