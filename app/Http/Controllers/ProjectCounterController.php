<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectCounterController extends Controller
{
    public function show(Project $project)
    {
        $this->authorize('update', $project);
        

        if (!$project->counter_id) {
            $project->counter_id = $this->generateCounterId();
            $project->counter_code = $this->generateCounterCode($project->counter_id);
            $project->save();
            // $project->update([
            //     'counter_id' => $this->generateCounterId(),
            //     'counter_code' => $this->generateCounterCode($project)
            // ]);
            //$project->refresh();
        }
        
        return view('projects.counter', compact('project'));
    }

    protected function generateCounterId()
    {
        return 'cnt_' . Str::random(16);
    }

    protected function generateCounterCode($counterId)
    {
        $counterDomain = config('app.analytics_domain'); // analytics.yourdomain.com
        
        return <<<HTML
        <!-- Site Analytics Counter -->
        <script>
        (function(w, d, s, h, id) {
            w.satProjectId = id; 
            w.satHost = h;
            var p = d.location.protocol === "https:" ? "https://" : "http://";
            var u = /_sat_session=[^;]+/.test(d.cookie) 
                ? "/js/tracker.js" 
                : "/api/init/" + id + "?ref=" + encodeURIComponent(d.referrer) + "&url=" + encodeURIComponent(d.location.href);
            var j = d.createElement(s); 
            j.async = 1; 
            j.src = p + h + u;
            var f = d.getElementsByTagName(s)[0];
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', '$counterDomain', '$counterId');
        </script>
        <noscript>
            <img src="https://$counterDomain/api/pixel/$counterId?noscript=1" style="display:none">
        </noscript>
        <!-- End Site Analytics Counter -->
        HTML;
    }
}