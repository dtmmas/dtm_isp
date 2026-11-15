<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MapAsset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'zone_id',
        'node_id',
        'name',
        'code',
        'asset_type',
        'asset_status',
        'latitude',
        'longitude',
        'description',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function node()
    {
        return $this->belongsTo(Node::class);
    }
}
