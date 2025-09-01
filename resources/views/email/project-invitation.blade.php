@component('mail::message')
# Приглашение в проект {{ $project->name }}

Вы получили приглашение присоединиться к проекту **{{ $project->name }}** ({{ $project->domain }}).

@component('mail::button', ['url' => $acceptUrl])
Принять приглашение
@endcomponent

Ссылка действительна в течение 7 дней. Если вы не ожидали это приглашение, просто проигнорируйте это письмо.

Спасибо,<br>
{{ config('app.name') }}
@endcomponent