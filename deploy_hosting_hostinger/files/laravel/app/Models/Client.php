<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'ip',
        'clave_wifi',
        'reference',
        'node_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class)
        ->using(ClientProduct::class)
        ->withTimestamps()
        ->withPivot('id','date_instalation','address','reference','latitude','longitude','amount_installation','advance','status','node_id','installation_photos');
    }

    public function client_products()
    {
        return $this->hasMany(ClientProduct::class);
    }

    public function invoices()
    {
        return $this->hasManyThrough(Invoice::class, ClientProduct::class);
    }

    public function tickets()
    {
        return $this->hasManyThrough(Ticket::class, ClientProduct::class, 'client_id', 'client_product_id');
    }

    public function getFirtsAndLastMonthDeudaAndPrice($client_product_id, $status = ['1','0'])
    {
        $invoices = Invoice::select(['invoices.id', 'invoices.created_at', 'invoices.status', 'invoices.price', 'invoices.client_product_id', 'invoices.date_expiration'])->whereIn('invoices.status',$status)->Where('invoices.client_product_id',$client_product_id)->WhereRaw('client_product_id IN (SELECT id FROM client_product WHERE client_id = ?)',[$this->id])->orderBy('created_at')->get();
        $meses = "";
        $monto = 0;
        $fechaFormateada = '';

        if(count($invoices)>0){
            $meses = "{$invoices->first()->getMesInvoice()}".((count($invoices)>1)?" a {$invoices->last()->getMesInvoice()}.":".");
            $monto =$invoices->sum('price');
            $fecha = $invoices->last()->date_expiration;

            if ($fecha) {
                $fechaObjeto = new \DateTime($fecha);
                $fechaFormateada = $fechaObjeto->format('Y-m-d');
            }
        }

        return ["Meses"=>$meses, "Valor"=>$monto, "Vencimiento" => $fechaFormateada];
    }

    public function invoicesVencidas()
    {
        return Invoice::select('invoices.id')->Where('invoices.status','-1')->WhereRaw('client_product_id IN (SELECT id FROM client_product WHERE client_id = ?)',[$this->id])->count();
    }

    public function invoicesVencidasMontoTotal()
    {
        return Invoice::select('invoices.price')->Where('invoices.status','-1')->WhereRaw('client_product_id IN (SELECT id FROM client_product WHERE client_id = ?)',[$this->id])->sum('invoices.price');
    }

    public function HtmlEstatus($activo, $style='text')
    {
        switch ($activo) {
            
            case '1':
                $color = 'success';
                $text = 'Activo';
                break;
                
            case '0':
                $color = 'danger';
                $text = 'Moroso';
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
}
