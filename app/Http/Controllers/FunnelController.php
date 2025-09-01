<?
namespace App\Http\Controllers;

use App\Models\FunnelStage;
use Illuminate\Http\Request;

class FunnelController extends Controller
{
    public function index() {
        $stages = FunnelStage::where('user_id', auth()->id())
            ->orderBy('position')->get();
        return view('funnels.index', compact('stages'));
    }

    public function create() {
        return view('funnels.create');
    }

    public function store(Request $request) {
        $request->validate([
            'title' => 'required',
            'position' => 'required|integer'
        ]);

        FunnelStage::create($request->all() + ['user_id' => auth()->id()]);
        return redirect()->route('funnels.index');
    }

    public function edit(FunnelStage $funnel) {
        return view('funnels.edit', ['stage' => $funnel]);
    }

    public function update(Request $request, FunnelStage $funnel) {
        $funnel->update($request->all());
        return redirect()->route('funnels.index');
    }

    public function destroy(FunnelStage $funnel) {
        $funnel->delete();
        return redirect()->route('funnels.index');
    }
}