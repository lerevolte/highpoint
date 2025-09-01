<?
namespace App\Mail;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProjectInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $project;
    public $token;

    public function __construct(Project $project, $token)
    {
        $this->project = $project;
        $this->token = $token;
    }

    public function build()
    {
        return $this->subject('Приглашение в проект '.$this->project->name)
                    ->markdown('emails.project-invitation')
                    ->with([
                        'project' => $this->project,
                        'acceptUrl' => route('invitations.accept', $this->token)
                    ]);
    }
}