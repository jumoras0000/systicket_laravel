<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'project_id',
        'status',
        'priority',
        'type',
        'estimated_hours',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'estimated_hours' => 'decimal:2',
        ];
    }

    // Relations
    public function projet()
    {
        return $this->belongsTo(Projet::class, 'project_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'ticket_user');
    }

    public function temps()
    {
        return $this->hasMany(Temps::class);
    }

    public function commentaires()
    {
        return $this->hasMany(Commentaire::class);
    }

    public function validations()
    {
        return $this->hasMany(Validation::class);
    }

    // Accesseurs calculés
    public function getSpentHoursAttribute(): float
    {
        return (float) $this->temps()->sum('hours');
    }

    public function getRemainingHoursAttribute(): float
    {
        return max(0, $this->estimated_hours - $this->spent_hours);
    }

    public function getClientNameAttribute(): ?string
    {
        return $this->projet?->client?->full_name;
    }

    public function getAssigneeNamesAttribute(): string
    {
        return $this->assignees->pluck('full_name')->implode(', ');
    }

    // Scopes
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['new', 'in-progress', 'waiting-client']);
    }

    public function scopeToValidate($query)
    {
        return $query->whereIn('status', ['to-validate', 'validated', 'refused']);
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->whereHas('projet', fn($q) => $q->where('client_id', $clientId));
    }

    public function scopeForProjects($query, array $projectIds)
    {
        return $query->whereIn('project_id', $projectIds);
    }
}
