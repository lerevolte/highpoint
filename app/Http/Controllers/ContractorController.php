<?

namespace App\Http\Controllers;

use App\Models\Contractor;
use Illuminate\Http\Request;

class ContractorController extends Controller
{
    public function index() {
        $contractors = Contractor::where('user_id', auth()->id())->get();
        return view('contractors.index', compact('contractors'));
    }

    public function create() {
        return view('contractors.create');
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required',
            'channel' => 'required',
            'budget' => 'required|numeric',
        ]);

        Contractor::create($request->all() + ['user_id' => auth()->id()]);

        return redirect()->route('contractors.index');
    }

    public function edit(Contractor $contractor) {
        return view('contractors.edit', compact('contractor'));
    }

    public function update(Request $request, Contractor $contractor) {
        $contractor->update($request->all());
        return redirect()->route('contractors.index');
    }

    public function destroy(Contractor $contractor) {
        $contractor->delete();
        return redirect()->route('contractors.index');
    }
}