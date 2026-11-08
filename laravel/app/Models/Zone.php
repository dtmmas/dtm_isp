<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\RoleCatalog;

class Zone extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
    ];

    public function collectors()
    {
        return $this->belongsToMany(User::class, 'collector_zone', 'zone_id', 'collector_id')
            ->role(RoleCatalog::ROLE_COBRADOR)
            ->withTimestamps();
    }

    public function nodes()
    {
        return $this->hasMany(Node::class);
    }
}
