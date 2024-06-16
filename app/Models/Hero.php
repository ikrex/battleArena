<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hero extends Model
{
    const TYPE = "type";
    const HELATH = "health";
    const ARENA_ID = "arena_id";

    const TYPE_ARCHER = "archer";
    const TYPE_CHILVARY = "cavalry";
    const TYPE_SWORDSMAN = "swordsman";

    use HasFactory;

    protected $fillable = [self::TYPE, self::HELATH, self::ARENA_ID];

    public function arena()
    {
        return $this->belongsTo(Arena::class);
    }
}
