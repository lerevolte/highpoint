<?php

// Основные контроллеры приложения
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectTeamController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ProjectCounterController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\IntegrationController;

// Контроллеры интеграций
use App\Http\Controllers\Integrations\YandexMetrikaController;
use App\Http\Controllers\Integrations\Bitrix24IntegrationController;

// Контроллеры аналитики
use App\Http\Controllers\Analytics\ChannelGroupingController;
use App\Http\Controllers\Analytics\ExpenseInputController;
use App\Http\Controllers\Analytics\FunnelReportController;
use App\Http\Controllers\Analytics\RomiReportController;
use App\Http\Controllers\Report\MultiChannelReportController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- Публичные и базовые маршруты ---
Route::get('/', fn() => view('welcome'));
//require __DIR__.'/auth.php';
Auth::routes();
// --- Маршруты, требующие аутентификации ---
Route::middleware('auth')->group(function () {

    // Профиль пользователя
    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::patch('/', 'update')->name('update');
        Route::delete('/', 'destroy')->name('destroy');
    });

    // Список проектов (главная страница после входа)
    Route::get('/dashboard', [ProjectController::class, 'index'])->name('dashboard');
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::resource('projects', ProjectController::class)->except(['index']);
    
    // Принятие приглашений
    Route::get('/invitations/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');
    
    // База знаний (получение статьи по slug)
    Route::get('/kb/fetch', [KnowledgeBaseController::class, 'fetchArticle'])->name('kb.fetch');

    // --- Группа маршрутов для конкретного проекта ---
    Route::prefix('projects/{project}')->group(function () {

        // --- Управление проектом ---
        Route::name('projects.')->group(function() {
            Route::get('/team', [ProjectTeamController::class, 'index'])->name('team.index');
            Route::post('/team/invite', [ProjectTeamController::class, 'invite'])->name('projects.team.invite');
            Route::post('/team/{user}/permissions', [ProjectTeamController::class, 'updatePermissions'])->name('projects.team.update-permissions');
            Route::delete('/team/{user}', [ProjectTeamController::class, 'remove'])->name('projects.team.remove');
            Route::get('/counter', [ProjectCounterController::class, 'show'])->name('counter');
            Route::put('/counter', [ProjectCounterController::class, 'update'])->name('projects.counter.update');
        });

        // --- Интеграции ---
        Route::controller(IntegrationController::class)->prefix('integrations')->name('projects.integrations.')->group(function () {
            Route::get('/', 'index')->name('index');

            // Яндекс.Метрика
            Route::prefix('yandex-metrika')->name('yandex-metrika.')->group(function() {
                Route::get('/connect', [YandexMetrikaController::class, 'connect'])->name('connect');
                Route::get('/{integration}/edit', [YandexMetrikaController::class, 'edit'])->name('edit');
                Route::put('/{integration}', [YandexMetrikaController::class, 'update'])->name('update');
                Route::delete('/{integration}', [YandexMetrikaController::class, 'destroy'])->name('destroy');
            });

            // Битрикс24
            Route::controller(Bitrix24IntegrationController::class)->prefix('bitrix24')->name('bitrix24.')->group(function () {
                Route::post('/', 'store')->name('store');
                Route::get('/{integration}/edit', 'edit')->name('edit');
                Route::put('/{integration}', 'update')->name('update');
            });
        });

        // --- Аналитика ---
        Route::prefix('analytics')->name('analytics.')->group(function () {
            
            // --- Отчеты ---
            Route::get('multi-channel-sequences', [MultiChannelReportController::class, 'index'])->name('multi-channel-sequences');
            Route::controller(MultiChannelReportController::class)->prefix('multi-channel-sequences')->name('multi-channel-sequences.')->group(function () {
                Route::get('/sankey-data', 'sankeyData')->name('sankey');
                Route::get('/export-csv', 'exportCsv')->name('export');
            });

            Route::get('romi-report', [RomiReportController::class, 'index'])->name('romi-report');
            Route::controller(RomiReportController::class)->prefix('romi-report')->name('romi-report.')->group(function () {
                Route::get('/chart-data', 'chartData')->name('chart-data');
                Route::get('/export-csv', 'exportCsv')->name('export');
            });
            
            Route::get('sales-funnel', [FunnelReportController::class, 'index'])->name('sales-funnel');
            
            // --- Настройки аналитики ---
            Route::controller(ExpenseInputController::class)->prefix('expense-input')->name('expense-input.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::delete('/{cost}', 'destroy')->name('destroy');
            });

            Route::controller(ChannelGroupingController::class)->prefix('channel-grouping')->name('channel-grouping.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
                Route::post('/{group}/rules', 'addRule')->name('rules.add');
                Route::delete('/rules/{rule}', 'removeRule')->name('rules.remove');
                Route::delete('/{group}', 'destroy')->name('destroy');
            });
        });
    });
});




/*use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectTeamController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ProjectCounterController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\Integrations\YandexMetrikaController;
use App\Http\Controllers\Integration\Bitrix24IntegrationController;
use App\Http\Controllers\Report\MultiChannelReportController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\Analytics\ChannelGroupingController;
use App\Http\Controllers\Analytics\ExpenseInputController;
use App\Http\Controllers\Analytics\RomiReportController;
use App\Http\Controllers\Analytics\FunnelReportController;

//use App\Http\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContractorController;
use App\Http\Controllers\FunnelController;
use App\Http\Controllers\AdController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\AiController;


Route::get('/', function () {
    return view('welcome');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/integrations/yandex-metrika/callback', [YandexMetrikaController::class, 'callback'])
     ->name('yandex-metrika.callback');
// Личный кабинет
Route::middleware(['auth'])->group(function () {

    Route::get('/', [ProjectController::class, 'index'])->name('dashboard');
    // Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('contractors', ContractorController::class);
    Route::resource('funnels', FunnelController::class);
    Route::resource('ads', AdController::class);
    Route::resource('reports', ReportController::class);
    Route::resource('plans', PlanController::class);
    Route::get('/ai/insights', [AiController::class, 'insights'])->name('ai.insights');
    Route::get('/kb/fetch', [KnowledgeBaseController::class, 'fetchArticle'])->name('kb.fetch');





    // Проекты пользователя
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    
    // Управление проектом
    Route::prefix('projects/{project}')->group(function () {
        Route::get('/', [ProjectController::class, 'show'])->name('projects.show');
        Route::get('/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/', [ProjectController::class, 'update'])->name('projects.update');
        
        // Управление доступом
        Route::get('/team', [ProjectTeamController::class, 'index'])->name('projects.team.index');
        Route::post('/team/invite', [ProjectTeamController::class, 'invite'])->name('projects.team.invite');
        Route::post('/team/{user}/permissions', [ProjectTeamController::class, 'updatePermissions'])->name('projects.team.update-permissions');
        Route::delete('/team/{user}', [ProjectTeamController::class, 'remove'])->name('projects.team.remove');


        Route::get('/counter', [ProjectCounterController::class, 'show'])->name('projects.counter');
        Route::put('/counter', [ProjectCounterController::class, 'update'])->name('projects.counter.update');
    });

    // Интеграции проекта
    Route::prefix('projects/{project}/integrations')->group(function () {
        // Список интеграций
        Route::get('/', [IntegrationController::class, 'index'])->name('projects.integrations.index');
        
        // Яндекс.Метрика
        Route::prefix('yandex-metrika')->group(function () {
            // Инициирование OAuth-процесса
            Route::get('/connect', [YandexMetrikaController::class, 'connect'])->name('projects.integrations.yandex-metrika.connect');
            
            // Callback для OAuth
            // Route::get('/callback', [YandexMetrikaController::class, 'callback'])
            //      ->name('projects.integrations.yandex-metrika.callback');
            
            // Настройка интеграции (выбор счетчиков)
            Route::get('/{integration}/edit', [YandexMetrikaController::class, 'edit'])->name('projects.integrations.yandex-metrika.edit');
            Route::put('/{integration}', [YandexMetrikaController::class, 'update'])->name('projects.integrations.yandex-metrika.update');
            
            // Отключение интеграции
            Route::delete('/{integration}', [YandexMetrikaController::class, 'destroy'])->name('projects.integrations.yandex-metrika.destroy');
        });

        Route::prefix('bitrix24')->name('projects.integrations.bitrix24.')->group(function () {
            Route::get('/{integration}/edit', [Bitrix24IntegrationController::class, 'edit'])->name('edit');
            Route::put('/{integration}', [Bitrix24IntegrationController::class, 'update'])->name('update');
            Route::post('/', [Bitrix24IntegrationController::class, 'store'])->name('store');
        });
    });
    // Принятие приглашения
    Route::get('/invitations/{token}', [InvitationController::class, 'accept'])->name('invitations.accept');
    
    // Профиль пользователя
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::prefix('projects/{project}/analytics')->group(function () {

        Route::get('multi-channel-sequences', [MultiChannelReportController::class, 'index'])->name('analytics.multi-channel-sequences');
        Route::get('multi-channel-sequences/sankey-data', [MultiChannelReportController::class, 'sankeyData'])->name('analytics.multi-channel-sequences.sankey');
        Route::get('multi-channel-sequences/export-csv', [MultiChannelReportController::class, 'exportCsv'])->name('analytics.multi-channel-sequences.export');

        Route::get('romi-report', [RomiReportController::class, 'index'])->name('analytics.romi-report');
        Route::get('romi-report/chart-data', [RomiReportController::class, 'chartData'])->name('analytics.romi-report.chart-data');
        Route::get('romi-report/export-csv', [RomiReportController::class, 'exportCsv'])->name('analytics.romi-report.export');

        Route::get('sales-funnel', [FunnelReportController::class, 'index'])->name('analytics.sales-funnel');
        
    
        Route::get('expense-input', [\App\Http\Controllers\Analytics\AnalyticsController::class, 'expenseInput'])->name('analytics.expense-input');
        Route::get('product', [\App\Http\Controllers\Analytics\AnalyticsController::class, 'productAnalytics'])->name('analytics.product');
        Route::get('channel-grouping', [\App\Http\Controllers\Analytics\AnalyticsController::class, 'channelGrouping'])->name('analytics.channel-grouping');
        Route::get('custom-dashboard', [\App\Http\Controllers\Analytics\AnalyticsController::class, 'customDashboard'])->name('analytics.custom-dashboard');


        Route::get('channel-grouping', [ChannelGroupingController::class, 'index'])->name('analytics.channel-grouping');
        Route::post('channel-grouping', [ChannelGroupingController::class, 'store'])->name('analytics.channel-grouping.store');
        Route::post('channel-grouping/{group}/rules', [ChannelGroupingController::class, 'addRule'])->name('analytics.channel-grouping.rules.add');
        Route::delete('channel-grouping/rules/{rule}', [ChannelGroupingController::class, 'removeRule'])->name('analytics.channel-grouping.rules.remove');
        Route::delete('channel-grouping/{group}', [ChannelGroupingController::class, 'destroy'])->name('analytics.channel-grouping.destroy');

        Route::get('expense-input', [ExpenseInputController::class, 'index'])->name('analytics.expense-input');
        Route::post('expense-input', [ExpenseInputController::class, 'store'])->name('analytics.expense-input.store');
        Route::delete('expense-input/{cost}', [ExpenseInputController::class, 'destroy'])->name('analytics.expense-input.destroy');
    });



});*/
