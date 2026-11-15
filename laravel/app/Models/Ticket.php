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
        'ticket_type_id',
        'title',
        'description',
        'priority',
        'status',
        'status_note',
    ];

    protected $appends = [
        'priority_html',
        'status_html',
        'client_name',
        'plan_name',
        'type_name',
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

    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }

    public function getClientNameAttribute()
    {
        return optional(optional(optional($this->clientProduct)->client)->user)->name_complete;
    }

    public function getPlanNameAttribute()
    {
        return optional(optional($this->clientProduct)->product)->name;
    }

    public function getTypeNameAttribute()
    {
        return optional($this->ticketType)->name ?: $this->title;
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

        return '<span class="badge bg-'.$color.' rounded px-2 py-1">'.$text.'</span>';
    }

    public function getStatusHtmlAttribute()
    {
        switch ($this->status) {
            case 'resuelto':
                $color = 'success';
                $text = 'Resuelto';
                break;
            case 'cancelado':
                $color = 'danger';
                $text = 'Cancelado';
                break;
            case 'cerrado':
                $color = 'success';
                $text = 'Resuelto';
                break;
            case 'en_proceso':
                $color = 'warning';
                $text = 'Pendiente';
                break;
            case 'abierto':
            case 'pendiente':
                $color = 'warning';
                $text = 'Pendiente';
                break;
            default:
                $color = 'warning';
                $text = 'Pendiente';
                break;
        }

        return '<span class="badge bg-'.$color.' rounded px-2 py-1">'.$text.'</span>';
    }
}
