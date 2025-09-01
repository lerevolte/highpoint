<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->string('device_type')->nullable()->after('ip_address');
            $table->boolean('is_touch_device')->nullable()->after('device_type');
            $table->string('browser_name')->nullable()->after('is_touch_device');
            $table->string('browser_version')->nullable()->after('browser_name');
            $table->string('operating_system')->nullable()->after('browser_version');
            $table->integer('color_depth')->nullable()->after('operating_system');
            $table->decimal('pixel_ratio', 3, 1)->nullable()->after('color_depth');
            $table->integer('scroll_depth')->nullable()->after('pixel_ratio');
            $table->integer('time_on_page')->nullable()->after('scroll_depth');
            $table->boolean('is_bounce')->default(true)->after('time_on_page');
        });
    }

    public function down()
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn([
                'device_type',
                'is_touch_device',
                'browser_name',
                'browser_version',
                'operating_system',
                'color_depth',
                'pixel_ratio',
                'scroll_depth',
                'time_on_page',
                'is_bounce'
            ]);
        });
    }
};