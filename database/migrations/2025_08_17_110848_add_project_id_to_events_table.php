<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            // Проверяем и добавляем project_id
            if (!Schema::hasColumn('events', 'project_id')) {
                $table->foreignId('project_id')
                      ->after('id')
                      ->constrained()
                      ->onDelete('cascade');
            }

            // Проверяем и добавляем visit_id (КЛЮЧЕВОЕ ИСПРАВЛЕНИЕ)
            if (!Schema::hasColumn('events', 'visit_id')) {
                $table->foreignId('visit_id')
                      ->after('project_id')
                      ->constrained()
                      ->onDelete('cascade');
            }

            // Проверяем и добавляем event_name
            if (!Schema::hasColumn('events', 'event_name')) {
                $table->string('event_name')->after('visit_id');
            }

            // Проверяем и добавляем event_data
            if (!Schema::hasColumn('events', 'event_data')) {
                $table->json('event_data')->nullable()->after('event_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            // Удаляем в обратном порядке, чтобы избежать ошибок с внешними ключами
            if (Schema::hasColumn('events', 'event_data')) {
                $table->dropColumn('event_data');
            }
            if (Schema::hasColumn('events', 'event_name')) {
                $table->dropColumn('event_name');
            }
            // Добавляем удаление visit_id
            if (Schema::hasColumn('events', 'visit_id')) {
                $table->dropForeign(['visit_id']);
                $table->dropColumn('visit_id');
            }
            if (Schema::hasColumn('events', 'project_id')) {
                $table->dropForeign(['project_id']);
                $table->dropColumn('project_id');
            }
        });
    }
};
