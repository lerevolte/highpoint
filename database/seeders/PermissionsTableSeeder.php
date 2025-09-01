<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $permissions = [
            'Аналитика' => [
                ['name' => 'Просмотр отчетов и показателей', 'slug' => 'view_analytics'],
                ['name' => 'Создание и редактирование отчетов и показателей', 'slug' => 'manage_analytics'],
                ['name' => 'Просмотр сделок', 'slug' => 'view_deals']
            ],
            'Коллтрекинг' => [
                ['name' => 'Просмотр истории звонков и тегов', 'slug' => 'view_call_history'],
                ['name' => 'Просмотр контактных данных клиентов', 'slug' => 'view_client_contacts'],
                ['name' => 'Прослушивание звонков', 'slug' => 'listen_calls']
            ]
        ];
        
        foreach ($permissions as $group => $items) {
            foreach ($items as $item) {
                Permission::create([
                    'group' => $group,
                    'name' => $item['name'],
                    'slug' => $item['slug']
                ]);
            }
        }
    }
}
