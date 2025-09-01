<?
namespace App\Models\Integrations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YandexMetrikaIntegration extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'access_token',
        'refresh_token',
        'expires_in',
        'token_created_at',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'json',
        'token_created_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function isTokenExpired()
    {
        return now()->diffInSeconds($this->token_created_at) > $this->expires_in;
    }
}