<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MapFiberRoute extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'zone_id',
        'node_id',
        'name',
        'route_type',
        'stroke_color',
        'stroke_weight',
        'path_points',
        'description',
    ];

    protected $casts = [
        'path_points' => 'array',
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
