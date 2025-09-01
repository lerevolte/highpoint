/**
 * Site Analytics Tracker v3.0 (Merged)
 *
 * Поддерживает асинхронную очередь команд, сбор событий,
 * и углубленный сбор метрик поведения (скролл, время на странице).
 */
(function(window, document) {
    'use strict';

    // 1. --- Инициализация и проверка ---
    const projectId = window.satProjectId;
    const host = window.satHost;
    if (!projectId || !host) {
        console.error('Site Analytics: Project ID or Host is not defined.');
        return;
    }

    // 2. --- Хранилище данных и API ---
    const dataStore = {
        events: [],
        user_id: null,
        metrika_client_id: null,
    };

    const commandHandler = function(command) {
        if (!command || !Array.isArray(command) || command.length === 0) return;
        const action = command[0];
        const params = command.slice(1);

        switch (action) {
            case 'event':
                if (params[0]) dataStore.events.push({ name: params[0], data: params[1] || null });
                break;
            case 'set':
                if (params[0] && typeof params[1] !== 'undefined' && dataStore.hasOwnProperty(params[0])) {
                    dataStore[params[0]] = params[1];
                }
                break;
            default:
                console.warn('Site Analytics: Unknown command', action);
        }
    };

    // 3. --- Сбор метрик поведения (Скролл и Время) ---
    let maxScrollDepth = 0;
    let startTime = Date.now();
    let activeTime = 0;
    let pageHidden = false;

    function trackScrollDepth() {
        const scrollHeight = document.documentElement.scrollHeight;
        const clientHeight = document.documentElement.clientHeight;
        if (scrollHeight <= clientHeight) {
            maxScrollDepth = 100;
            return;
        }
        const currentScroll = window.scrollY || window.pageYOffset;
        const currentDepth = Math.round((currentScroll / (scrollHeight - clientHeight)) * 100);
        maxScrollDepth = Math.max(maxScrollDepth, currentDepth);
    }

    function handleVisibilityChange() {
        if (document.hidden) {
            activeTime += Date.now() - startTime;
            pageHidden = true;
        } else {
            startTime = Date.now();
            pageHidden = false;
        }
    }

    // 4. --- Вспомогательные функции для сбора данных ---
    function getBrowserData() {
        const ua = navigator.userAgent;
        const getBrowserName = (ua) => {
            if (ua.includes('Firefox')) return 'Firefox';
            if (ua.includes('SamsungBrowser')) return 'Samsung Browser';
            if (ua.includes('Opera') || ua.includes('OPR/')) return 'Opera';
            if (ua.includes('Trident')) return 'IE';
            if (ua.includes('Edge')) return 'Edge';
            if (ua.includes('Chrome')) return 'Chrome';
            if (ua.includes('Safari')) return 'Safari';
            return 'Unknown';
        };
        const getOS = (ua) => {
            if (ua.includes('Windows')) return 'Windows';
            if (ua.includes('Mac OS')) return 'MacOS';
            if (ua.includes('Linux')) return 'Linux';
            if (ua.includes('Android')) return 'Android';
            if (ua.includes('like Mac OS X')) return 'iOS';
            return 'Unknown';
        };
        return {
            device_type: /Mobi|Android/i.test(ua) ? 'mobile' : 'desktop',
            is_touch: 'ontouchstart' in window || navigator.maxTouchPoints > 0,
            browser: { name: getBrowserName(ua), version: (ua.match(/(Firefox|Chrome|Safari|Opera|OPR|Edge|MSIE|Trident(?=\/))\/?\s*(\d+)/i) || [])[2] || 'Unknown' },
            os: getOS(ua),
        };
    }

    function getUTMParams() {
        const params = new URLSearchParams(window.location.search);
        return {
            utm_source: params.get('utm_source'),
            utm_medium: params.get('utm_medium'),
            utm_campaign: params.get('utm_campaign'),
            utm_term: params.get('utm_term'),
            utm_content: params.get('utm_content'),
        };
    }

    // 5. --- Основная функция сбора и отправки данных ---
    const sendData = function() {
        // Собираем все данные в один объект
        const docElem = document.documentElement;
        const screen = window.screen;
        const nav = window.navigator;
        
        // Рассчитываем итоговое время на странице
        const finalActiveTime = activeTime + (pageHidden ? 0 : Date.now() - startTime);

        // Генерируем или получаем ID сессии
        let sessionId = sessionStorage.getItem('_sat_session');
        let isNewSession = false;
        if (!sessionId) {
            sessionId = Date.now() + '.' + Math.random().toString(36).substring(2);
            sessionStorage.setItem('_sat_session', sessionId);
            isNewSession = true;
        }

        const payload = {
            // Основные данные
            project_id: projectId,
            domain: window.location.hostname,
            url: window.location.href,
            referrer: document.referrer,
            user_agent: nav.userAgent,
            language: nav.language,
            session_id: sessionId,
            is_new_session: isNewSession,
            
            // Данные о странице
            title: document.title,
            path: window.location.pathname,
            query: window.location.search,
            hash: window.location.hash,
            
            // Технические данные
            ...getBrowserData(),
            ...getUTMParams(),
            screen_width: screen.width,
            screen_height: screen.height,
            color_depth: screen.colorDepth,
            pixel_ratio: window.devicePixelRatio,
            viewport_width: docElem.clientWidth,
            viewport_height: docElem.clientHeight,
            cookies_enabled: nav.cookieEnabled,
            timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
            
            // Метрики поведения
            scroll_depth: maxScrollDepth,
            time_on_page: finalActiveTime,

            // Данные из API
            ...dataStore,
        };

        const endpoint = `${document.location.protocol}//${host}/api/collect`;
        const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
        navigator.sendBeacon(endpoint, blob);
    };

    // 6. --- Инициализация и запуск ---
    // Настраиваем API
    window.sat = window.sat || {};
    window.sat.q = window.sat.q || [];
    while (window.sat.q.length > 0) {
        commandHandler(window.sat.q.shift());
    }
    window.sat.push = commandHandler;

    // Вешаем обработчики для сбора метрик
    window.addEventListener('scroll', trackScrollDepth, { passive: true });
    document.addEventListener('visibilitychange', handleVisibilityChange);
    
    // Отправляем данные при уходе со страницы - это самый надежный способ
    // захватить все события, скролл и время на странице.
    window.addEventListener('pagehide', sendData, { capture: true });

})(window, document);
