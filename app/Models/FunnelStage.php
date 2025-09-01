<?

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FunnelStage extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'position', 'user_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}

