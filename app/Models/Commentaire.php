<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commentaire extends Model
{
    protected $fillable = [
        'rapport_id',
        'user_id',
        'type',
        'contenu',
    ];

    public function rapport()
    {
        return $this->belongsTo(Rapport::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}