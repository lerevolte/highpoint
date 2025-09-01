<?
namespace App\Http\Controllers\Integrations;

use App\Models\Project;
use App\Models\Integrations\YandexMetrikaIntegration;
use App\Services\YandexMetrikaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class YandexMetrikaController extends Controller
{
    protected $metrikaService;
    
    public function __construct(YandexMetrikaService $metrikaService)
    {
        $this->metrikaService = $metrikaService;
    }
    
    // public function connect(Project $project)
    // {
    //     $clientId = config('services.yandex_metrika.client_id');
    //     $state = json_encode(['project' => $project->id]);
    //     $redirectUri = route('projects.integrations.yandex-metrika.callback', ['project' => $project]);

    //     $authUrl = "https://oauth.yandex.ru/authorize?" . http_build_query([
    //         'response_type' => 'code',
    //         'client_id' => $clientId,
    //         'state' => $state,
    //         //'redirect_uri' => $redirectUri,
    //     ]);
        
    //     return redirect($authUrl);
    // }
    public function connect(Project $project)
    {
        $clientId = config('services.yandex_metrika.client_id');
        $state = json_encode([
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'time' => now()->timestamp
        ]);
        
        // Подписываем state для безопасности
        $state = encrypt($state);
        
        $redirectUri = route('yandex-metrika.callback');
        
        $authUrl = "https://oauth.yandex.ru/authorize?" . http_build_query([
            'response_type' => 'code',
            'client_id' => $clientId,
            'state' => $state,
            'redirect_uri' => $redirectUri,
        ]);
        
        return redirect($authUrl);
    }
    
    public function callback(Request $request)
    {
        if (!$request->has('code') || !$request->has('state')) {
            dd($request->all());
            return redirect('/')->with('error', 'Authorization failed: missing parameters');
        }

        try {
            $state = decrypt($request->state);
            $state = json_decode($state, true);
            
            if (!isset($state['project_id'])) {
                // Сохраняем project_id в сессии для редиректа
                session()->flash('error', 'Invalid project in callback');
                return redirect('/');
            }
            
            $project = Project::find($state['project_id']);
            
            if (!$project) {
                session()->flash('error', 'Project not found');
                return redirect('/');
            }

            // Получаем токен доступа
            $tokenData = $this->metrikaService->getAccessToken($request->code);
            
            // Создаем или обновляем интеграцию
            $integration = YandexMetrikaIntegration::updateOrCreate(
                ['project_id' => $project->id],
                [
                    'user_id' => Auth::id(),
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'],
                    'expires_in' => $tokenData['expires_in'],
                    'token_created_at' => now(),
                ]
            );
            
            // Получаем список счетчиков
            $counters = $this->metrikaService->getCounters($tokenData['access_token']);
            
            // Сохраняем счетчики во временное хранилище
            session()->put('yandex_metrika_counters', $counters);
            
            return redirect()->route('projects.integrations.yandex-metrika.edit', [
                'project' => $project,
                'integration' => $integration,
            ]);


        } catch (\Exception $e) {
            dd($e->getMessage());
            \Log::error('Yandex Metrika callback error: ' . $e->getMessage());
            session()->flash('error', 'Authorization failed: ' . $e->getMessage());
            return redirect('/');
        }
    }


    // public function callback(Request $request, Project $project)
    // {
    // 	if (!$request->has('code') || !$request->has('state')) {
	//         return redirect()->route('projects.integrations.index', $project)
	//                ->with('error', 'Authorization failed: missing parameters');
	//     }
	    
	//     $state = json_decode($request->state, true);
	    
	//     if (!isset($state['project_id']) || $state['project_id'] != $project->id) {
	//         return redirect()->route('projects.integrations.index', $project)
	//                ->with('error', 'Invalid project in callback');
	//     }

    //     $this->validate($request, [
    //         'code' => 'required',
    //         'state' => 'required',
    //     ]);
        
    //     $state = json_decode($request->state, true);
    //     $project = Project::findOrFail($state['project']);
        
    //     // Получаем токен доступа
    //     $tokenData = $this->metrikaService->getAccessToken($request->code);
        
    //     // Создаем или обновляем интеграцию
    //     $integration = YandexMetrikaIntegration::updateOrCreate(
    //         ['project_id' => $project->id],
    //         [
    //             'user_id' => Auth::id(),
    //             'access_token' => $tokenData['access_token'],
    //             'refresh_token' => $tokenData['refresh_token'],
    //             'expires_in' => $tokenData['expires_in'],
    //             'token_created_at' => now(),
    //         ]
    //     );
        
    //     // Получаем список счетчиков
    //     $counters = $this->metrikaService->getCounters($tokenData['access_token']);
        
    //     // Сохраняем счетчики во временное хранилище
    //     session()->put('yandex_metrika_counters', $counters);
        
    //     return redirect()->route('projects.integrations.yandex-metrika.edit', [
    //         'project' => $project,
    //         'integration' => $integration,
    //     ]);
    // }
    
    public function edit(Project $project, YandexMetrikaIntegration $integration)
    {
        $counters = session()->get('yandex_metrika_counters', []);
        
        if (empty($counters)) {
            // Если счетчики не в сессии, пробуем получить их снова
            $counters = $this->metrikaService->getCounters($integration->access_token);
        }
        
        $selectedCounters = $integration->settings['counters'] ?? [];
        
        return view('projects.integrations.yandex-metrika.edit', compact(
            'project',
            'integration',
            'counters',
            'selectedCounters'
        ));
    }
    
    public function update(Request $request, Project $project, YandexMetrikaIntegration $integration)
    {
        $validated = $request->validate([
            'counters' => 'required|array',
            'counters.*' => 'integer',
        ]);
        
        $settings = $integration->settings ?? [];
        $settings['counters'] = $validated['counters'];
        
        $integration->update([
            'settings' => $settings,
            'is_active' => true,
        ]);
        
        return redirect()->route('projects.integrations.index', $project)
            ->with('success', 'Интеграция с Яндекс.Метрикой успешно настроена');
    }
    
    public function destroy(Project $project, YandexMetrikaIntegration $integration)
    {
        $integration->delete();
        
        return redirect()->route('projects.integrations.index', $project)
            ->with('success', 'Интеграция с Яндекс.Метрикой отключена');
    }
}