<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'last_name',
        'first_name',
        'email',
        'password',
        'role',
        'status',
        'phone',
        'last_login',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'last_login' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Accesseur pour le nom complet
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Vérifier si l'utilisateur a un rôle
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCollaborateur(): bool
    {
        return $this->role === 'collaborateur';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    // Relations
    public function projetsAsClient()
    {
        return $this->hasMany(Projet::class, 'client_id');
    }

    public function projetsAsManager()
    {
        return $this->hasMany(Projet::class, 'manager_id');
    }

    public function projets()
    {
        return $this->belongsToMany(Projet::class, 'projet_user');
    }

    public function tickets()
    {
        return $this->belongsToMany(Ticket::class, 'ticket_user');
    }

    public function createdTickets()
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    public function tempsEntries()
    {
        return $this->hasMany(Temps::class);
    }

    public function validations()
    {
        return $this->hasMany(Validation::class);
    }

    public function commentaires()
    {
        return $this->hasMany(Commentaire::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeCollaborateurs($query)
    {
        return $query->where('role', 'collaborateur')->active();
    }

    public function scopeEquipe($query)
    {
        return $query->whereIn('role', ['admin', 'collaborateur'])->active();
    }

    public function scopeClients($query)
    {
        return $query->where('role', 'client')->active();
    }
}
