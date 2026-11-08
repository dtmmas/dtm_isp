<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description', 
    ];

    public function subcategories(){
        return $this->hasMany(Subcategory::class);
    }

    public function products()
    {
    	return $this->hasManyThrough(Product::class, Subcategory::class);
    }
}
