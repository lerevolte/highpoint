<?php
// Файл: app/Http/Requests/Api/CollectAnalyticsRequest.php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CollectAnalyticsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // Основные данные (обязательные)
            'project_id' => 'required|string|exists:projects,counter_id',
            'domain' => 'required|string|max:255',
            'url' => 'required|string|max:2048',
            'user_agent' => 'required|string|max:512',
            'session_id' => 'required|string|max:64',
            'is_new_session' => 'required|boolean',

            // Идентификаторы (необязательные, приходят от API)
            'user_id' => 'nullable|string|max:255',
            'metrika_client_id' => 'nullable|string|max:255',

            // Данные о странице (необязательные)
            'referrer' => 'nullable|string|max:2048',
            'title' => 'nullable|string|max:255',
            'path' => 'nullable|string|max:2048',
            'query' => 'nullable|string|max:2048',
            'hash' => 'nullable|string|max:2048',
            'language' => 'nullable|string|max:10',

            // Технические данные об устройстве
            'device_type' => 'nullable|string|in:desktop,mobile',
            'is_touch' => 'nullable|boolean',
            'cookies_enabled' => 'nullable|boolean',
            'browser' => 'nullable|array',
            'browser.name' => 'nullable|string',
            'browser.version' => 'nullable|string',
            'os' => 'nullable|string|max:50',
            'screen_width' => 'nullable|integer',
            'screen_height' => 'nullable|integer',
            'viewport_width' => 'nullable|integer',
            'viewport_height' => 'nullable|integer',
            'color_depth' => 'nullable|integer',
            'pixel_ratio' => 'nullable|numeric',
            'timezone' => 'nullable|string|max:100',

            // Поведенческие метрики
            'scroll_depth' => 'nullable|integer|min:0|max:100',
            'time_on_page' => 'nullable|integer|min:0',

            // UTM-метки
            'utm_source' => 'nullable|string|max:100',
            'utm_medium' => 'nullable|string|max:100',
            'utm_campaign' => 'nullable|string|max:100',
            'utm_term' => 'nullable|string|max:100',
            'utm_content' => 'nullable|string|max:100',

            // События
            'events' => 'nullable|array',
        ];
    }
}