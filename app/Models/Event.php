<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'visit_id',
        'event_name',
        'event_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'event_data' => 'array', // Автоматически преобразует JSON в массив и обратно
    ];

    /**
     * Получить проект, к которому относится событие.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Получить визит, к которому относится событие.
     */
    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }
}
