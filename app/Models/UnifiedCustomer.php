<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnifiedCustomer extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Получить все идентификаторы, связанные с этим единым профилем.
     */
    public function identities()
    {
        return $this->hasMany(CustomerIdentity::class);
    }

    /**
     * Получить проект, к которому относится профиль.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}