<?php

namespace App\Jobs;

use App\Models\Visit;
use App\Models\UnifiedCustomer;
use App\Models\CustomerIdentity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StitchUserDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $visit;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Visit $visit)
    {
        $this->visit = $visit;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 1. Собираем все доступные идентификаторы из визита
        $identities = $this->collectIdentitiesFromVisit();

        if (empty($identities)) {
            // Если у визита нет идентификаторов для склейки, ничего не делаем
            // Можно добавить привязку к unified_customer_id в саму таблицу visits
            return;
        }

        Log::info("Stitching visit ID: {$this->visit->id} with identities:", $identities);

        // 2. Ищем существующий профиль по этим идентификаторам
        $existingCustomer = $this->findExistingCustomer($identities);

        DB::transaction(function () use ($existingCustomer, $identities) {
            if ($existingCustomer) {
                // 3a. Если профиль найден, добавляем к нему новые идентификаторы
                $this->addNewIdentities($existingCustomer, $identities);
            } else {
                // 3b. Если профиль не найден, создаем новый
                $this->createNewCustomer($identities);
            }
        });

        // TODO: В будущем здесь можно добавить логику для объединения (мерджа)
        // нескольких профилей, если один визит связал их вместе.
    }

    /**
     * Собирает все идентификаторы из визита в стандартизированный массив.
     */
    private function collectIdentitiesFromVisit(): array
    {
        $identities = [];

        if ($this->visit->user_id) {
            $identities['user_id'] = $this->visit->user_id;
        }
        if ($this->visit->metrika_client_id) {
            $identities['metrika_client_id'] = $this->visit->metrika_client_id;
        }
        if ($this->visit->session_id) {
            $identities['session_id'] = $this->visit->session_id;
        }
        // TODO: Можно добавить логику для извлечения email/phone из событий

        return $identities;
    }

    /**
     * Ищет существующий UnifiedCustomer по массиву идентификаторов.
     */
    private function findExistingCustomer(array $identities)
    {
        $identityValues = array_values($identities);
        $identityTypes = array_keys($identities);

        return UnifiedCustomer::whereHas('identities', function ($query) use ($identityValues, $identityTypes) {
            $query->whereIn('identity_type', $identityTypes)
                  ->whereIn('identity_value', $identityValues);
        })->first();
    }

    /**
     * Добавляет новые, еще не существующие идентификаторы к профилю.
     */
    private function addNewIdentities(UnifiedCustomer $customer, array $identities): void
    {
        $existingIdentities = $customer->identities()
            ->whereIn('identity_type', array_keys($identities))
            ->pluck('identity_value', 'identity_type')
            ->toArray();

        foreach ($identities as $type => $value) {
            if (!isset($existingIdentities[$type]) || $existingIdentities[$type] !== $value) {
                $customer->identities()->create([
                    'identity_type' => $type,
                    'identity_value' => $value,
                ]);
            }
        }
    }

    /**
     * Создает новый UnifiedCustomer и привязывает к нему все идентификаторы.
     */
    private function createNewCustomer(array $identities): void
    {
        $customer = UnifiedCustomer::create([
            'project_id' => $this->visit->project_id,
            'crm_user_id' => $identities['user_id'] ?? null, // Основной ID
        ]);

        foreach ($identities as $type => $value) {
            $customer->identities()->create([
                'identity_type' => $type,
                'identity_value' => $value,
            ]);
        }
    }
}
