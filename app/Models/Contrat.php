<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrat extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'project_id',
        'client_id',
        'hours',
        'rate',
        'status',
        'start_date',
        'end_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'hours' => 'decimal:2',
            'rate' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function projet()
    {
        return $this->belongsTo(Projet::class, 'project_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function getConsumedHoursAttribute(): float
    {
        if (!$this->project_id) return 0;
        return (float) Temps::where('project_id', $this->project_id)
            ->whereHas('ticket', fn($q) => $q->where('status', 'validated'))
            ->sum('hours');
    }

    public function getRemainingHoursAttribute(): float
    {
        return max(0, $this->hours - $this->consumed_hours);
    }

    public function getLinkedTickets()
    {
        if (!$this->project_id) return collect();
        return Ticket::where('project_id', $this->project_id)
            ->with('temps')
            ->orderByDesc('created_at')
            ->get();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
