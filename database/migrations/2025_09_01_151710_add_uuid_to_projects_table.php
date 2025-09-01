<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->nullable()->after('id');
        });

        // Заполняем UUID для существующих записей
        DB::table('projects')->whereNull('uuid')->cursor()->each(function ($project) {
            DB::table('projects')
                ->where('id', $project->id)
                ->update(['uuid' => Str::uuid()]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};