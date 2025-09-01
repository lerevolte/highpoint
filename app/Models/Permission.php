<?
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['group', 'name', 'slug'];
}