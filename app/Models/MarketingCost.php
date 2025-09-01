<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class MarketingCost extends Model {
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts = ['date' => 'date'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}