<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Node extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'name',
        'zone_id', 
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function client_products(){
        return $this->hasMany(ClientProduct::class);
    }
}
