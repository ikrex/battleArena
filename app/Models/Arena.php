<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Arena extends Model
{
    use HasFactory;

    public function heroes()
    {
        return $this->hasMany(Hero::class);
    }
    
}
