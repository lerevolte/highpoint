<?
namespace App\Console\Commands;

use App\Models\Integrations\YandexMetrikaIntegration;
use App\Services\YandexMetrikaService;
use Illuminate\Console\Command;

class RefreshYandexMetrikaTokens extends Command
{
    protected $signature = 'yandex-metrika:refresh-tokens';
    protected $description = 'Refresh expired Yandex Metrika access tokens';
    
    public function handle(YandexMetrikaService $metrikaService)
    {
        $integrations = YandexMetrikaIntegration::where('is_active', true)
            ->where('token_created_at', '<', now()->subSeconds(3600 * 6)) // Обновляем заранее
            ->get();
            
        foreach ($integrations as $integration) {
            try {
                $tokenData = $metrikaService->refreshToken($integration->refresh_token);
                
                $integration->update([
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'],
                    'expires_in' => $tokenData['expires_in'],
                    'token_created_at' => now(),
                ]);
                
                $this->info("Token refreshed for project {$integration->project_id}");
            } catch (\Exception $e) {
                $this->error("Failed to refresh token for project {$integration->project_id}: " . $e->getMessage());
            }
        }
    }
}