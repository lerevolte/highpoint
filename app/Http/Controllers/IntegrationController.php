<?
namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    public function index(Project $project)
    {
        $integrations = $project->integrations()->with('user')->get();
        
        return view('projects.integrations.index', compact('project', 'integrations'));
    }
}