<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Traits\FunctionGeneral;

class ClientProduct extends Pivot
{
    use FunctionGeneral;

    protected $fillable = [
        'client_id',
        'product_id',
        'node_id',
        'date_instalation',
        'address',
        'reference',
        'latitude',
        'longitude',
        'amount_installation',
        'advance',
        'status',
        'payments_advancement',
        'discounted_month',
        'installation_photos',
    ];

    protected $casts = [
        'installation_photos' => 'array',
    ];

    protected $appends = [
        'status_html','advance_html', 'amount_installation_formatted'
    ];

    public function getStatusHtmlAttribute()
    {  
        switch ($this->status) {
            case '-1':
                $color = 'danger';
                $text = 'Cancelado';
                break;
            
            case '1':
                $color = 'success';
                $text = 'Activo';
                break;
                
            case '0':
                $color = 'warning';
                $text = 'Suspendido';
                break;

            default:
                $color = 'default';
                $text = 'Indefinido';
                break;
        }
        return '<span class="badge bg-'.$color.' rounded">'.$text.'</span>';
    }

    public function getAdvanceHtmlAttribute()
    {  
        switch ($this->advance) {
            
            case '1':
                $color = 'success';
                $text = 'Si';
                break;
                
            case '0':
                $color = 'danger';
                $text = 'No';
                break;

            default:
                $color = 'default';
                $text = 'Indefinido';
                break;
        }
        return '<span class="badge bg-'.$color.' rounded">'.$text.'</span>';
    }

    public function getAmountInstallationFormattedAttribute()
    {  
        return $this->formatearNumero($this->amount_installation);
    }

    public function getInstallationPhotoUrlsAttribute()
    {
        $photos = is_array($this->installation_photos) ? $this->installation_photos : [];

        return collect($photos)->map(function ($photo) {
            return asset('laravel/storage/app/public/'.ltrim($photo, '/'));
        })->values()->all();
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'client_product_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function node()
    {
        return $this->belongsTo(Node::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'client_product_id');
    }
}
