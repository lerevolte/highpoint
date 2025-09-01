<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Integration;
use App\Models\Project;
use App\Models\UnifiedCustomer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessBitrix24WebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Project $project,
        public Integration $integration,
        public array $data
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $eventType = $this->data['event'];
        $fields = $this->data['data']['FIELDS'];

        // Мы обрабатываем только обновление сделки
        if ($eventType !== 'ONCRMDEALUPDATE') {
            return;
        }

        // Проверяем, что сделка успешна (ID стадии может отличаться)
        // 'WON' - это стандартный системный ID для успешной сделки.
        if (!str_contains($fields['STAGE_ID'], 'WON')) {
            return;
        }

        try {
            DB::transaction(function () use ($fields) {
                // 1. Получаем полную информацию о сделке, чтобы извлечь контакты и UTM
                $dealInfo = $this->makeApiCall('crm.deal.get', ['id' => $fields['ID']]);
                if (!$dealInfo) return;
                
                $contactId = $dealInfo['CONTACT_ID'] ?? null;
                if (!$contactId) return;

                // 2. Получаем информацию о контакте, чтобы извлечь email/телефон
                $contactInfo = $this->makeApiCall('crm.contact.get', ['id' => $contactId]);
                if (!$contactInfo) return;

                $email = collect($contactInfo['EMAIL'])->first()['VALUE'] ?? null;
                $phone = collect($contactInfo['PHONE'])->first()['VALUE'] ?? null;
                
                if (!$email && !$phone) return; // Не можем идентифицировать клиента

                // 3. Находим или создаем единый профиль клиента
                $customer = UnifiedCustomer::firstOrCreate(
                    [
                        'project_id' => $this->project->id,
                        'email' => $email, // Ищем в первую очередь по email
                    ],
                    [ 'phone' => $phone]
                );

                // (Опционально) Привязываем телефон, если его не было
                if ($phone && !$customer->phone) {
                    $customer->update(['phone' => $phone]);
                }

                // 4. Создаем событие "Покупка из CRM"
                Event::create([
                    'project_id' => $this->project->id,
                    'visit_id' => null, // У этого события нет прямого визита
                    'event_name' => 'purchase_crm',
                    'value' => $dealInfo['OPPORTUNITY'] ?? 0.0,
                    'event_data' => json_encode([
                        'crm' => 'bitrix24',
                        'deal_id' => $fields['ID'],
                        'email' => $email,
                        'phone' => $phone,
                        'utm_source' => $dealInfo['UTM_SOURCE'] ?? null,
                        'utm_medium' => $dealInfo['UTM_MEDIUM'] ?? null,
                        'utm_campaign' => $dealInfo['UTM_CAMPAIGN'] ?? null,
                        'utm_term' => $dealInfo['UTM_TERM'] ?? null,
                        'utm_content' => $dealInfo['UTM_CONTENT'] ?? null,
                    ]),
                    'created_at' => now(), // Используем текущее время как время покупки
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Failed to process Bitrix24 webhook.', [
                'project_id' => $this->project->id,
                'error' => $e->getMessage(),
            ]);
            $this->fail($e);
        }
    }

    /**
     * Вспомогательная функция для API-запросов к Битрикс24.
     */
    private function makeApiCall(string $method, array $params = [])
    {
        $webhookUrl = $this->integration->settings['webhook_url'];
        $url = rtrim($webhookUrl, '/') . '/' . $method;

        $response = Http::post($url, $params);

        if ($response->successful()) {
            return $response->json()['result'] ?? null;
        }

        Log::error('Bitrix24 API call failed.', [
            'method' => $method,
            'params' => $params,
            'response_status' => $response->status(),
            'response_body' => $response->body()
        ]);
        return null;
    }
}

