<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VersionRapport extends Model
{
    protected $table = 'version_rapports';

    protected $fillable = [
        'rapport_id',
        'numero_version',
        'file_url',
        'date_upload',
    ];

    public function rapport()
    {
        return $this->belongsTo(Rapport::class);
    }
}