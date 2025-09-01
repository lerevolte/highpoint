<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('events', function (Blueprint $table) {
            $table->decimal('value', 15, 2)->nullable()->after('event_data');
            $table->string('currency', 10)->nullable()->after('value');
        });
    }
    public function down() {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['value', 'currency']);
        });
    }
};
