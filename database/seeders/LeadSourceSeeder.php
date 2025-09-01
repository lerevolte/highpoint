<?php

namespace Database\Seeders;

use App\Models\LeadSource;
use App\Models\Project;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeadSourceSeeder extends Seeder
{
    public function run()
    {
        $project = Project::find(1);

        if (!$project) {
            $this->command->info('Основной проект не найден, источники не созданы.');
            return;
        }

        $sources = [
            ['source' => 'google', 'medium' => 'cpc'],
            ['source' => 'yandex', 'medium' => 'cpc'],
            ['source' => 'google', 'medium' => 'organic'],
            ['source' => 'yandex', 'medium' => 'organic'],
            ['source' => 'vk.com', 'medium' => 'social'],
            ['source' => 'facebook.com', 'medium' => 'social'],
            ['source' => '(direct)', 'medium' => '(none)'],
        ];

        foreach ($sources as $source) {
            LeadSource::firstOrCreate([
                'project_id' => $project->id,
                'source' => $source['source'],
                'medium' => $source['medium'],
            ]);
        }
        
        $this->command->info('Тестовые источники лидов созданы.');
    }
}

