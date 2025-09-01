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
        Schema::table('visits', function (Blueprint $table) {
            // 1. Добавляем недостающие поля из tracker.js
            $table->string('page_title')->nullable()->after('url');
            $table->string('page_path')->nullable()->after('page_title');
            $table->text('page_query')->nullable()->after('page_path'); // text, т.к. query может быть длинным
            $table->string('page_hash')->nullable()->after('page_query');
            $table->integer('viewport_width')->nullable()->after('screen_height');
            $table->integer('viewport_height')->nullable()->after('viewport_width');
            $table->boolean('cookies_enabled')->nullable()->after('is_touch_device');
            $table->string('timezone', 100)->nullable()->after('language');

            // 2. Увеличиваем длину полей, которые могут быть длинными
            $table->string('url', 2048)->change();
            $table->string('referrer', 2048)->nullable()->change();
            $table->string('user_agent', 512)->change();

            // 3. Делаем поле is_bounce более гибким (теперь оно не рассчитывается на фронтенде)
            $table->boolean('is_bounce')->nullable()->default(null)->change();
            
            // 4. Убеждаемся, что поля для идентификаторов существуют (на случай, если предыдущая миграция не была запущена)
            if (!Schema::hasColumn('visits', 'user_id')) {
                $table->string('user_id')->nullable()->after('project_id')->index();
            }
            if (!Schema::hasColumn('visits', 'metrika_client_id')) {
                $table->string('metrika_client_id')->nullable()->after('user_id')->index();
            }

            $table->json('metadata')->nullable()->after('timezone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn([
                'page_title',
                'page_path',
                'page_query',
                'page_hash',
                'viewport_width',
                'viewport_height',
                'cookies_enabled',
                'timezone',
            ]);

            // Возвращаем старые типы данных
            $table->string('url', 255)->change();
            $table->string('referrer', 255)->nullable()->change();
            $table->string('user_agent', 255)->change();
            $table->boolean('is_bounce')->default(true)->change();
        });
    }
};
