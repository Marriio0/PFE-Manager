<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InviteCode extends Model
{
    protected $fillable = ['code', 'role', 'used', 'used_by', 'created_by'];

    protected $casts = ['used' => 'boolean'];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function usedBy()
    {
        return $this->belongsTo(User::class, 'used_by');
    }
}