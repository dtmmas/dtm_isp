<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\FunctionGeneral;

class Payment extends Model
{
    use HasFactory, SoftDeletes;
    use FunctionGeneral;

    protected $guarded = [
        'status',
    ];

    protected $appends = [
        'reportado_por','numero','status_html','price_formatted', 'method_payment_html', 'name_client', 'name_product', 'address_plan',
    ];
    public function getNumeroAttribute()
    {
        return str_pad($this->id.date('Ym', strtotime($this->created_at)), 8, "0", STR_PAD_LEFT);
    }

    public function getReportadoPorAttribute()
    {
        if($this->invoice->client_product->client->user->id != $this->user_id){
            return $this->user->name_complete;
        }
        return '';
    }

    public function getNameClientAttribute()
    {
        return $this->invoice->client_product->client->user->name_complete;
    }

    public function getNameProductAttribute()
    {
        return $this->invoice->client_product->product->name;
    }

    public function getAddressPlanAttribute()
    {
        return $this->invoice->client_product->address;
    }

    public function getPriceFormattedAttribute()
    {  
        return $this->formatearNumero($this->price);
    }

    public function getMethodPaymentHtmlAttribute()
    {  
        switch ($this->method_payment) {
            case '1':
                $color = 'primary';
                $text = 'Deposito';
                break;
            
            case '2':
                $color = 'info';
                $text = 'Tarjeta';
                break;
            
            case '3':
                $color = 'success';
                $text = 'Efectivo';
                break;

            default:
                $color = 'default';
                $text = 'Indefinido';
                break;
        }
        return '<span class="badge bg-'.$color.' rounded">'.$text.'</span>';
    }

    public function getStatusHtmlAttribute()
    {  
        switch ($this->status) {
            case '-1':
                $color = 'danger';
                $text = 'Rechazado';
                break;
            
            case '1':
                $color = 'success';
                $text = 'Aprobado';
                break;
                
            case '0':
                $color = 'warning';
                $text = 'Pendiente';
                break;

            default:
                $color = 'default';
                $text = 'Indefinido';
                break;
        }
        return '<span class="badge bg-'.$color.' rounded">'.$text.'</span>';
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //totales
    public function totalPayment($tipo)
    {
        switch ($tipo) {
            case '1':
                $total = self::where('status','1')
                    ->where('method_payment','1')
                    ->sum('price');
                $total = $this->formatearNumero($total);
                break;

            case '2':
                $total = self::where('status','1')
                ->where('method_payment','2')
                ->sum('price');
                $total = $this->formatearNumero($total);
                break;

            case '3':
                $total = self::where('status','1')
                ->where('method_payment','3')
                ->sum('price');
                $total = $this->formatearNumero($total);
                break;
            
            default:
                $total = self::where('status','0')
                ->sum('price');
                $total = $this->formatearNumero($total);
                break;
        }

        return $total;
    }

    public function getReportadoPor()
    {
        if($this->invoice->client_product->client->user->id != $this->user_id){
            return $this->user->name_complete;
        }
        return 'Cliente';
    }

    public function getAprobadoPor()
    {
        if($this->invoice->client_product->client->user->id != $this->processed_by){
            return $this->user->name_complete;
        }
        return 'Cliente';
    }
}
