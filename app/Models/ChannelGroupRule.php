<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ChannelGroupRule extends Model {
    use HasFactory;
    protected $guarded = ['id'];
    public function channelGroup() { return $this->belongsTo(ChannelGroup::class); }
}
