<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\FunctionGeneral;

class Product extends Model
{
    use HasFactory, SoftDeletes, FunctionGeneral;

    protected $fillable = [
        'name',
        'velocidad',
        'price', 
    ];

    protected $appends = [
        'price_formatted',
    ];

    public function getPriceFormattedAttribute()
    {  
        return $this->formatearNumero($this->price);
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class)->withTimestamps();;
    }

}
