<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Models\Project;
use Illuminate\Http\Request;

class Bitrix24IntegrationController extends Controller
{
    /**
     * Отображает форму для редактирования настроек Bitrix24.
     */
    public function edit(Project $project, Integration $integration)
    {
        // Проверяем, что это действительно интеграция Bitrix24
        if ($integration->type !== 'bitrix24') {
            abort(404);
        }

        return view('projects.integrations.bitrix24.edit', [
            'project' => $project,
            'integration' => $integration,
        ]);
    }

    /**
     * Обновляет настройки интеграции с Bitrix24.
     */
    public function update(Request $request, Project $project, Integration $integration)
    {
        $request->validate([
            'webhook_url' => 'required|url|regex:/https?:\/\/[a-zA-Z0-9-]+\.bitrix24\.[a-z]+\/rest\//',
        ]);
        
        $integration->update([
            'settings' => [
                'webhook_url' => $request->webhook_url,
            ],
        ]);

        return redirect()->route('projects.integrations.index', $project)->with('success', 'Настройки Битрикс24 успешно обновлены!');
    }

    /**
     * Сохраняет новую интеграцию с Bitrix24.
     */
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'webhook_url' => 'required|url|regex:/https?:\/\/[a-zA-Z0-9-]+\.bitrix24\.[a-z]+\/rest\//',
        ]);

        $project->integrations()->updateOrCreate(
            ['type' => 'bitrix24'],
            [
                'name' => 'Битрикс24',
                'settings' => ['webhook_url' => $request->webhook_url],
                'user_id' => auth()->id(),
            ]
        );

        return back()->with('success', 'Интеграция с Битрикс24 успешно сохранена!');
    }
}

