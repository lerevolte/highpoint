<?php

namespace Database\Seeders;

use App\Models\KnowledgeBaseArticle;
use Illuminate\Database\Seeder;

class KnowledgeBaseSeeder extends Seeder
{
    public function run()
    {
        KnowledgeBaseArticle::updateOrCreate(
            ['slug' => 'sankey-diagram-explained'],
            [
                'title' => 'Что такое диаграмма Сэнки?',
                'content' => "
<p class='mb-4'><b>Диаграмма Сэнки</b> — это тип блок-схемы, в которой ширина стрелок пропорциональна величине потока. Она идеально подходит для визуализации того, как пользователи или ресурсы перемещаются между различными этапами.</p>
<h4 class='font-semibold mt-4 mb-2'>Как читать эту диаграмму:</h4>
<ul class='list-disc list-inside space-y-2'>
    <li><b>Узлы (Nodes):</b> Прямоугольники представляют собой каналы или источники трафика (например, 'google / cpc' или '(direct) / (none)').</li>
    <li><b>Потоки (Links):</b> Линии, соединяющие узлы, показывают путь пользователей.</li>
    <li><b>Ширина потока:</b> Чем шире линия, тем больше пользователей прошло по данному маршруту.</li>
</ul>
<p class='mt-4'>Эта диаграмма помогает быстро определить самые популярные пути клиентов и выявить, какие каналы наиболее эффективно работают в связке друг с другом.</p>
"
            ]
        );
    }
}
