<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
