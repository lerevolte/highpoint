<?php

namespace App\Http\Controllers\Api;

use App\Models\Project;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VisitController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'url' => 'required|string|max:2048',
            'referrer' => 'nullable|string|max:2048',
            'screen_width' => 'nullable|integer',
            'screen_height' => 'nullable|integer',
            'language' => 'nullable|string|max:10',
            'utm_source' => 'nullable|string|max:100',
            'utm_medium' => 'nullable|string|max:100',
            'utm_campaign' => 'nullable|string|max:100',
            'session_id' => 'required|string|max:36',
            'is_new_session' => 'boolean',
            'event' => 'nullable|string',
            'event_data' => 'nullable|json'
        ]);

        try {
            $visit = Visit::create([
                'project_id' => $validated['project_id'],
                'tracker_domain' => parse_url($validated['url'], PHP_URL_HOST),
                'url' => $validated['url'],
                'referrer' => $validated['referrer'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'screen_width' => $validated['screen_width'],
                'screen_height' => $validated['screen_height'],
                'language' => $validated['language'],
                'utm_source' => $validated['utm_source'],
                'utm_medium' => $validated['utm_medium'],
                'utm_campaign' => $validated['utm_campaign'],
                'session_id' => $validated['session_id'],
                'is_new_session' => $validated['is_new_session'] ?? false
            ]);

            if (isset($validated['event'])) {
                // Обработка кастомных событий
                $visit->events()->create([
                    'name' => $validated['event'],
                    'data' => $validated['event_data']
                ]);
            }

            return response()->json(['status' => 'success'], 201);
        } catch (\Exception $e) {
            Log::error('Visit tracking error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }

    public function checkLastVisit(Request $request)
    {
        $visit = Visit::where('project_id', $request->project_id)
            ->latest()
            ->first();

        return response()->json([
            'success' => !!$visit,
            'visit' => $visit
        ]);
    }
}
