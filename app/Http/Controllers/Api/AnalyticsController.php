<?php

namespace App\Http\Controllers\Api;

use App\Models\Project;
use App\Models\Visit;
use App\Models\Event;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Api\CollectAnalyticsRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\StitchUserDataJob;

class AnalyticsController extends Controller
{
    public function init(Request $request, $projectId)
    {
        $project = Project::where('counter_id', $projectId)->firstOrFail();
        
        // Устанавливаем куки для новых посетителей
        return response()
            ->file(public_path('js/tracker.js'), [
                'Content-Type' => 'application/javascript',
                'Cache-Control' => 'no-cache'
            ]);
    }

    public function collect(CollectAnalyticsRequest $request)
    {
        $validated = $request->validated();

        $project = Project::where('counter_id', $validated['project_id'])->firstOrFail();

        $visit = null;
        DB::transaction(function () use ($validated, $project, $request, &$visit) {
            info($project->id);
            $visitData = [
                // --- Ключи и ID ---
                'project_id' => $project->id, // Правильный числовой ID
                'session_id' => $validated['session_id'],
                'user_id' => $validated['user_id'] ?? null,
                'metrika_client_id' => $validated['metrika_client_id'] ?? null,

                // --- Данные о странице ---
                'tracker_domain' => $validated['domain'],
                'url' => $validated['url'],
                'referrer' => $validated['referrer'] ?? null,
                'page_title' => $validated['title'] ?? null,
                'page_path' => $validated['path'] ?? null,
                'page_query' => $validated['query'] ?? null,
                'page_hash' => $validated['hash'] ?? null,
                'language' => $validated['language'] ?? null,

                // --- Технические данные ---
                'ip_address' => $request->ip(),
                'user_agent' => $validated['user_agent'],
                'device_type' => $validated['device_type'] ?? null,
                'is_touch_device' => $validated['is_touch'] ?? null,
                'cookies_enabled' => $validated['cookies_enabled'] ?? null,
                'browser_name' => $validated['browser']['name'] ?? null,
                'browser_version' => $validated['browser']['version'] ?? null,
                'operating_system' => $validated['os'] ?? null,
                'screen_width' => $validated['screen_width'] ?? null,
                'screen_height' => $validated['screen_height'] ?? null,
                'viewport_width' => $validated['viewport_width'] ?? null,
                'viewport_height' => $validated['viewport_height'] ?? null,
                'color_depth' => $validated['color_depth'] ?? null,
                'pixel_ratio' => $validated['pixel_ratio'] ?? null,
                'timezone' => $validated['timezone'] ?? null,

                // --- Поведенческие метрики ---
                'scroll_depth' => $validated['scroll_depth'] ?? null,
                'time_on_page' => $validated['time_on_page'] ?? null,
                'is_new_session' => $validated['is_new_session'],

                // --- Маркетинг (UTM) ---
                'utm_source' => $validated['utm_source'] ?? null,
                'utm_medium' => $validated['utm_medium'] ?? null,
                'utm_campaign' => $validated['utm_campaign'] ?? null,
                'utm_term' => $validated['utm_term'] ?? null,
                'utm_content' => $validated['utm_content'] ?? null,
                
                // --- Дополнительные данные ---
                'metadata' => json_encode(['headers' => $request->headers->all()]),
            ];

            // $fillableFields = (new Visit)->getFillable();
            // foreach ($validated as $key => $value) {
            //     if (in_array($key, $fillableFields)) {
            //         $visitData[$key] = $value;
            //     }
            // }

            // Фильтрация null-значений
            $visitData = array_filter($visitData, fn($value) => $value !== null);

            $visit = Visit::create($visitData);

            if (!empty($validated['events'])) {
                foreach ($validated['events'] as $eventData) {
                    $visit->events()->create([
                        'project_id' => $project->id,
                        'event_name' => $eventData['name'],
                        'event_data' => $eventData['data'] ?? null,
                    ]);
                }
            }
        });

        StitchUserDataJob::dispatch($visit);

        return response()->json([
            'status' => 'success',
            'visit_id' => $visit->id,
            'session_id' => $visit->session_id
        ], 201);
    }

    public function pixel(Request $request, $projectId)
    {
        // Обработка для noscript
        $project = Project::where('counter_id', $projectId)->firstOrFail();
        
        Visit::create([
            'project_id' => $project->id,
            'tracker_domain' => $request->header('Referer') ? parse_url($request->header('Referer'), PHP_URL_HOST) : 'direct',
            'url' => $request->header('Referer') ?? 'unknown',
            'is_noscript' => true
        ]);

        // Возвращаем прозрачный 1x1 пиксель
        $pixel = base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
        return response($pixel, 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store'
        ]);
    }
}