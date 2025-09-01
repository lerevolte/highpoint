@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Тестовая страница для пикселя аналитики</h1>
        <p>Эта страница поможет вам проверить отправку <code>user_id</code> и <code>metrika_client_id</code>.</p>
        <p><b>Важно:</b> Перед использованием замените <code>ВАШ_PROJECT_ID</code> и <code>ВАШ_ДОМЕН_АНАЛИТИКИ</code> в коде этой страницы на реальные значения.</p>

        <h2>Шаг 1: Имитация действий</h2>
        <button id="loginBtn">1. Имитировать вход пользователя</button>
        <button id="metrikaBtn">2. Имитировать получение ClientID Метрики</button>

        <h2>Шаг 2: Отправка тестового события</h2>
        <p>После установки ID, вы можете отправить тестовое событие. Данные об ID будут отправлены вместе с этим событием при уходе со страницы.</p>
        <button id="eventBtn">Отправить событие "Клик по кнопке"</button>

        <h2>Лог действий</h2>
        <div id="log">Лог будет отображаться здесь...</div>
    </div>

    <!-- 
      ===============================================================
      ВАШ КОД СЧЕТЧИКА АНАЛИТИКИ
      Замените 'cnt_ВАШ_PROJECT_ID' и 'analytics.your-domain.com'
      ===============================================================
    -->
    
    <!-- Конец кода счетчика -->


    

<!-- <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('You are logged in!') }}
                </div>
            </div>
        </div>
    </div>
</div> -->
<!-- Site Analytics Counter -->

<script>
(function(w, d, s, h, id) {
    w.satProjectId = id; 
    w.satHost = h;
    var p = d.location.protocol === "https:" ? "https://" : "http://";
    var u = /_sat_session=[^;]+/.test(d.cookie) 
        ? "/js/tracker.js" 
        : "/api/init/" + id + "?ref=" + encodeURIComponent(d.referrer) + "&url=" + encodeURIComponent(d.location.href);
    var j = d.createElement(s); 
    j.async = 1; 
    j.src = p + h + u;
    var f = d.getElementsByTagName(s)[0];
    f.parentNode.insertBefore(j, f);
})(window, document, 'script', 'b24-select.ru', 'cnt_t5vdUAaWWxDoaDQi');
</script>
<script>
        const logDiv = document.getElementById('log');

        function log(message) {
            console.log(message);
            logDiv.innerHTML += message + '\n';
        }

        // --- Обработчик для кнопки входа ---
        document.getElementById('loginBtn').addEventListener('click', function() {
            // Генерируем случайный ID пользователя для теста
            const testUserId = 'user_' + Math.random().toString(36).substring(2, 9);
            
            // Отправляем команду в API вашего трекера
            window.sat = window.sat || { q: [], push: function(arr){ this.q.push(arr) } };
            window.sat.push(['set', 'user_id', testUserId]);
            
            log(`> Установлен user_id: ${testUserId}`);
            this.disabled = true;
        });

        // --- Обработчик для кнопки Метрики ---
        document.getElementById('metrikaBtn').addEventListener('click', function() {
            // Генерируем случайный ClientID для теста
            const testMetrikaId = Math.floor(Date.now() / 1000) + '' + Math.floor(Math.random() * 1000000);

            // Отправляем команду в API вашего трекера
            window.sat = window.sat || { q: [], push: function(arr){ this.q.push(arr) } };
            window.sat.push(['set', 'metrika_client_id', testMetrikaId]);

            log(`> Установлен metrika_client_id: ${testMetrikaId}`);
            this.disabled = true;
        });

        // --- Обработчик для кнопки события ---
        document.getElementById('eventBtn').addEventListener('click', function() {
            const eventData = {
                button_text: this.innerText,
                timestamp: new Date().toISOString()
            };

            // Отправляем событие
            window.sat = window.sat || { q: [], push: function(arr){ this.q.push(arr) } };
            window.sat.push(['event', 'test_button_click', eventData]);

            log('> Отправлено событие "test_button_click"');
            log('   (Все собранные данные, включая ID, будут отправлены при закрытии страницы)');
        });

        log('Тестовая страница загружена.');
    </script>
<noscript>
    <img src="https://b24-select.ru/api/pixel/cnt_t5vdUAaWWxDoaDQi?noscript=1" style="display:none">
</noscript>
<!-- End Site Analytics Counter -->
@endsection
