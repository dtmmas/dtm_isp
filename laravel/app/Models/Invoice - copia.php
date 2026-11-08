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
        if($monto==0){
            $monto = $this->price;
        }
        try {
            $usd = file_get_contents(($hoy.'.txt'));
        } catch (\Throwable $th) {
            // header('Content-Type: text/html; charset=utf-8');   
            // try {
            //     $urlCT = "https://www.banguat.gob.gt/sites/default/files/banguat/cambio/tc.asp";
            //     $ch = curl_init($urlCT);
            //     curl_setopt($ch,CURLOPT_MAXREDIRS,10);
            //     curl_setopt($ch, CURLOPT_HEADER, false);
            //     curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            //     curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
            //     $cl = curl_exec($ch);
            //     $dom = new \DOMDocument();
            //     @$dom->loadHTML($cl);
            //     $xpath = new \DOMXpath($dom); 
            //     $txt = $xpath->query('//*[@id="contentClean"]/span/span');
            //     $usd=trim($txt->item(0)->anodeValue);
            //     file_put_contents($hoy.'.txt',$usd);
            // } catch (\Throwable $th) {
                $a = file_get_contents('https://www.banguat.gob.gt/sites/default/files/banguat/cambio/tc.asp');
                $a = explode('<span class="Arial">',$a);
                $a = explode('</span>',$a[1]);
                $usd = trim($a[0]);
                file_put_contents($hoy.'.txt',$usd);
            // }
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
