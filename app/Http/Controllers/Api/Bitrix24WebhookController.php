<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessBitrix24WebhookJob;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Bitrix24WebhookController extends Controller
{
    /**
     * Принимает и ставит в очередь вебхуки от Битрикс24.
     */
    public function handle(Request $request, Project $project)
    {
        // Проверяем, что для проекта настроена интеграция
        $integration = $project->integrations()->where('type', 'bitrix24')->first();
        if (!$integration) {
            Log::warning('Webhook received for project without bitrix24 integration.', ['project_id' => $project->id]);
            return response()->json(['status' => 'error', 'message' => 'Integration not configured'], 404);
        }

        // Получаем все данные из запроса
        $data = $request->all();
        
        // Простая валидация, чтобы убедиться, что это похоже на вебхук от Б24
        if (!isset($data['event']) || !isset($data['data']['FIELDS']['ID'])) {
             Log::warning('Invalid webhook payload received.', ['project_id' => $project->id, 'payload' => $data]);
             return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
        }

        // Отправляем задачу в очередь на обработку
        ProcessBitrix24WebhookJob::dispatch($project, $integration, $data);

        // Мгновенно отвечаем Битриксу, что все хорошо
        return response()->json(['status' => 'success']);
    }
}

