<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'client_id',
        'status',
        'start_date',
        'end_date',
        'manager_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    // Relations
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'projet_user');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'project_id');
    }

    public function contrats()
    {
        return $this->hasMany(Contrat::class, 'project_id');
    }

    public function activeContrat()
    {
        return $this->hasOne(Contrat::class, 'project_id')->where('status', 'active');
    }

    public function temps()
    {
        return $this->hasMany(Temps::class, 'project_id');
    }

    // Accesseurs
    public function getTicketsCountAttribute(): int
    {
        return $this->tickets()->count();
    }

    public function getTotalHoursAttribute(): float
    {
        return (float) $this->temps()
            ->whereHas('ticket', fn($q) => $q->where('status', 'validated'))
            ->sum('hours');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('manager_id', $userId)
              ->orWhereHas('users', fn($q2) => $q2->where('users.id', $userId));
        });
    }
}
