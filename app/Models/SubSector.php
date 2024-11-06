<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubSector extends Model
{
    use HasFactory;
    public function sector(){
        return $this->belongsTo(Sector::class,'sector_id');
    }
}
