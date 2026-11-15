<?php

namespace App\Models;

use App\Traits\FunctionGeneral;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use FunctionGeneral, SoftDeletes;

    protected $fillable = [
        'client_product_id',
        'user_id',
        'title',
        'description',
        'priority',
        'status',
    ];

    protected $appends = [
        'priority_html',
        'status_html',
        'client_name',
        'plan_name',
        'address_plan',
        'reported_by',
    ];

    public function clientProduct()
    {
        return $this->belongsTo(ClientProduct::class, 'client_product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getClientNameAttribute()
    {
        return optional(optional(optional($this->clientProduct)->client)->user)->name_complete;
    }

    public function getPlanNameAttribute()
    {
        return optional(optional($this->clientProduct)->product)->name;
    }

    public function getAddressPlanAttribute()
    {
        return optional($this->clientProduct)->address;
    }

    public function getReportedByAttribute()
    {
        return optional($this->user)->name_complete;
    }

    public function getPriorityHtmlAttribute()
    {
        switch ($this->priority) {
            case 'alta':
                $color = 'danger';
                $text = 'Alta';
                break;
            case 'media':
                $color = 'warning';
                $text = 'Media';
                break;
            default:
                $color = 'info';
                $text = 'Baja';
                break;
        }

        return '<span class="badge bg-'.$color.' rounded">'.$text.'</span>';
    }

    public function getStatusHtmlAttribute()
    {
        switch ($this->status) {
            case 'cerrado':
                $color = 'success';
                $text = 'Cerrado';
                break;
            case 'en_proceso':
                $color = 'primary';
                $text = 'En proceso';
                break;
            default:
                $color = 'warning';
                $text = 'Abierto';
                break;
        }

        return '<span class="badge bg-'.$color.' rounded">'.$text.'</span>';
    }
}
