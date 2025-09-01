<?php

namespace App\Observers;

use App\Models\Project;
use Illuminate\Support\Str;

class ProjectObserver
{
    /**
     * Handle the Project "creating" event.
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    public function creating(Project $project)
    {
        if (empty($project->counter_id)) {
            $project->counter_id = 'cnt_' . Str::random(16);
        }
        
        if (empty($project->counter_code)) {
            $project->counter_code = $this->generateCounterCode($project->counter_id);
        }
    }

    protected function generateCounterCode($counterId)
    {
        $counterDomain = config('app.analytics_domain', 'analytics.example.com');
        
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
