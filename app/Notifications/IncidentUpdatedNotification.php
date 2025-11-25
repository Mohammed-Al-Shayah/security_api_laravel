<?php

namespace App\Notifications;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class IncidentUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Incident $incident
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $incident = $this->incident;

        return [
            'type'         => 'incident_updated',
            'incident_id'  => $incident->id,
            'title'        => $incident->title,
            'status'       => $incident->status,
            'project_id'   => $incident->project_id,
            'project_name' => optional($incident->project)->name,
            'reporter_id'  => $incident->reporter_id,
            'reporter_name'=> optional($incident->reporter)->name,
            'occurred_at'  => optional($incident->occurred_at)?->toIso8601String(),
        ];
    }
}
