<?
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Integrations\YandexMetrikaIntegration;

class Project extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'currency',
        'domain',
        'counter_domain',
        'counter_id',
        'counter_code'
    ];
    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('is_admin', 'permissions', 'invitation_token');
    }
    
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function integrations()
    {
        return $this->hasMany(Integration::class);
    }

    public function yandexMetrikaIntegration()
    {
        return $this->hasOne(YandexMetrikaIntegration::class);
    }

    public function channelGroups()
    {
        return $this->hasMany(ChannelGroup::class);
    }

    public function marketingCosts()
    {
        return $this->hasMany(MarketingCost::class);
    }
    
}