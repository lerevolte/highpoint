<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerIdentity extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Получить единый профиль, к которому относится этот идентификатор.
     */
    public function unifiedCustomer()
    {
        return $this->belongsTo(UnifiedCustomer::class);
    }
}
