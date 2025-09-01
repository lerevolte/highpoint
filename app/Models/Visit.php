<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id']; // Используем guarded, чтобы разрешить массовое заполнение всех полей

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_new_session' => 'boolean',
        'is_touch_device' => 'boolean',
        'cookies_enabled' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Получить проект, к которому относится визит.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Получить все события, связанные с этим визитом.
     * * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
