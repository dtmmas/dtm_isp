<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MapCoverage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'zone_id',
        'node_id',
        'name',
        'coverage_type',
        'technology',
        'center_latitude',
        'center_longitude',
        'radius_meters',
        'polygon_points',
        'fill_color',
        'stroke_color',
        'description',
    ];

    protected $casts = [
        'polygon_points' => 'array',
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
