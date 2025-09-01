<?
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YandexMetrikaService
{
    public function getAccessToken($code)
    {
        $clientId = config('services.yandex_metrika.client_id');
        $clientSecret = config('services.yandex_metrika.client_secret');
        $redirectUri = route('yandex-metrika.callback');//route('projects.integrations.yandex-metrika.callback');
        
        $response = Http::asForm()
            ->withBasicAuth($clientId, $clientSecret)
            ->post('https://oauth.yandex.ru/token', [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirectUri,
            ]);
        
        if ($response->failed()) {
            Log::error('Yandex Metrika token request failed', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            throw new \Exception('Failed to get access token from Yandex Metrika');
        }
        
        return $response->json();
    }
    
    public function getCounters($accessToken)
    {
        $response = Http::withHeaders([
            'Authorization' => 'OAuth ' . $accessToken,
            'Content-Type' => 'application/x-yametrika+json',
        ])->get('https://api-metrika.yandex.net/management/v1/counters', [
            'field' => 'goals,mirrors,grants,filters,operations,counter_flags,measurement_tokens',
        ]);
        
        if ($response->failed()) {
            dd($response->body());
            Log::error('Yandex Metrika counters request failed', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            return [];
        }
        
        $data = $response->json();
        return $data['counters'] ?? [];
    }
    
    public function refreshToken($refreshToken)
    {
        $clientId = config('services.yandex_metrika.client_id');
        $clientSecret = config('services.yandex_metrika.client_secret');
        
        $response = Http::asForm()
            ->withBasicAuth($clientId, $clientSecret)
            ->post('https://oauth.yandex.ru/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ]);
        
        if ($response->failed()) {
            Log::error('Yandex Metrika token refresh failed', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            throw new \Exception('Failed to refresh Yandex Metrika token');
        }
        
        return $response->json();
    }
}