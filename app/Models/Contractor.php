<?

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contractor extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'channel', 'budget', 'user_id'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
