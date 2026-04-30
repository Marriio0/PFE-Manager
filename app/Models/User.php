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
        'nom', 'email', 'password', 'role', 'status',
        'cne', 'filiere', 'niveau', 'grade', 'departement',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['password' => 'hashed'];

    public function rapportsEtudiant()  { return $this->hasMany(Rapport::class, 'etudiant_id'); }
    public function rapportsEncadres()  { return $this->hasMany(Rapport::class, 'encadrant_id'); }
    public function commentaires()      { return $this->hasMany(Commentaire::class); }
    public function validations()       { return $this->hasMany(Validation::class); }
}