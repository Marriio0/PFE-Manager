<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
    protected $fillable = [
        'titre', 'description', 'date_depot', 'statut',
        'etudiant_id', 'encadrant_id',
        'verified_by_encadrant', 'verified_at',
    ];

    protected $casts = [
        'verified_by_encadrant' => 'boolean',
        'verified_at'           => 'datetime',
    ];

    public function etudiant()  { return $this->belongsTo(User::class, 'etudiant_id'); }
    public function encadrant() { return $this->belongsTo(User::class, 'encadrant_id'); }
    public function commentaires() { return $this->hasMany(Commentaire::class); }
    public function validations()  { return $this->hasMany(Validation::class); }
    public function versions()     { return $this->hasMany(VersionRapport::class); }
}