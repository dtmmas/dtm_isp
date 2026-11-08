<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\FunctionGeneral;

class Invoice extends Model
{
    use HasFactory,SoftDeletes, FunctionGeneral;

    protected $fillable = [
        'status',
        'created_at',
        'price',
        'date_expiration',
        'client_product_id', 
        'user_id',
        'payments_advancement',
        'discounted_month',
    ];

    protected $appends = [
        'status_html','date','date_update','price_formatted', 'name_client', 'name_product','payments_pending'
    ];

    public function getPaymentsPendingAttribute()
    {
       return $this->payments()->where('status','0')->count();
    }
    
    public function getNameProductAttribute()
    {
        return $this->client_product->product->name;
    }
    public function getNameClientAttribute()
    {
        return $this->client_product->client->user->name_complete;
    }

    public function getDateAttribute()
    {
        return $this->created_at->format('Y-m-d');
    }

    public function getDateUpdateAttribute()
    {
        return $this->updated_at->format('Y-m-d');
    }

    public function getMesInvoice()
    {

        $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");

        return $meses[$this->created_at->format('m')-1];
    }

    public function getPriceFormattedAttribute()
    {  
        return $this->formatearNumero($this->price);
    }
    
    public function getStatusHtmlAttribute()
    {  
        switch ($this->status) {
            case '-1':
                $color = 'danger';
                $text = 'Vencida';
                break;
            
            case '1':
                $color = 'success';
                $text = 'Pagada';
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

    public function statusVisualizacion($style='text')
    {  
        switch ($this->status) {
            case '-1':
                $color = 'danger';
                $text = 'Vencida';
                break;
            
            case '1':
                $color = 'success';
                $text = 'Pagada';
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
        if ($style=='html') {
            return '<span class="badge bg-'.$color.' rounded">'.$text.'</span>';
        } else {
            return $text;
        }
    }

    public function client_product()
    {
        return $this->belongsTo(ClientProduct::class, 'client_product_id');
    }
    
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function priceConversion($moneda='USD', $monto=0)
    {
        $hoy = date('Ymd');
        $cacheDirectory = storage_path('app/exchange-rates');
        $todayFilePath = $cacheDirectory . DIRECTORY_SEPARATOR . $hoy . '.txt';
        if($monto==0){
            $monto = $this->price;
        }
        try {
            $usd = file_get_contents($todayFilePath);
        } catch (\Throwable $th) {
            $daysAgo = 1;
            $usd = '7.70952';
            $maxIterations = 4;

            while ($daysAgo <= $maxIterations) {
                $formattedDate = date('Ymd', strtotime("-$daysAgo days"));
                $filePath = $cacheDirectory . DIRECTORY_SEPARATOR . $formattedDate . '.txt';
                if (file_exists($filePath)) {
                    $usd = file_get_contents($filePath);
                    break;
                } else {
                    $daysAgo++;
                }
            }

            if (! is_dir($cacheDirectory)) {
                @mkdir($cacheDirectory, 0775, true);
            }

            @file_put_contents($todayFilePath, $usd);
        }
      
        if($usd<5){
            $usd = 5;
        }
        switch ($moneda) {
            case 'USD':
                return number_format($monto/$usd,2,'.','');
                break;

            case 'Q':
                return number_format($monto*$usd,2,'.','');
                break;
            
            default:
                return number_format($monto/$usd,2,'.','');
                break;
        }
       
    }

    public function enablePayment()
    {
        return ($this->status!='1' && $this->id == $this->idInvoiceInicialSinPago());
    }

    public function idInvoiceInicialSinPago()
    {
        $invoice = Invoice::select(['invoices.id'])
            ->where('invoices.status', '!=', '1')
            ->where('invoices.client_product_id', $this->client_product_id)
            /*->whereNotIn('invoices.id', function ($query) {
                $query->select('invoice_id')
                    ->from('payments')
                    ->where('status', '=', '0');
            })*/
            ->orderBy('invoices.created_at')
            ->limit(1)
            ->first();

        if ($invoice) {
            $invoice_id = $invoice->id;
        } else {
            $invoice_id = $this->id;
        }
        return $invoice_id;
    }

    public function mesInvoiceInicialSinPago()
    {
        $invoice = Invoice::select(['invoices.created_at'])
            ->where('invoices.status', '!=', '1')
            ->where('invoices.client_product_id', $this->client_product_id)/*
            ->whereNotIn('invoices.id', function ($query) {
                $query->select('invoice_id')
                    ->from('payments')
                    ->where('status', '=', '0');
            })*/
            ->orderBy('invoices.created_at')
            ->limit(1)
            ->first();

        if ($invoice) {
            $monthName = $invoice->created_at->monthName;
        } else {
            $monthName = "";
        }
        return $monthName;
    }

}
