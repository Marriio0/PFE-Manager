<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Validation extends Model
{
    use HasFactory;

    protected $fillable = [
        'rapport_id',
        'user_id',
        'decision',
        'commentaire',
        'date_decision',
    ];

    protected $casts = [
        'date_decision' => 'date',
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