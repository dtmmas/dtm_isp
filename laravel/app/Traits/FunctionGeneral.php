<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

trait FunctionGeneral
{
    public function fechaActual()
    {
        return date('Y-m-d H:i:s');
    }

    public function fechaCortaActual()
    {
        return date('Y-m-d');
    }

    public function mypaginate($items, $perPage = 20, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function formatearNumero($monto,$simbolo = 'Q', $decimales=2)
    {
        return $simbolo.number_format($monto,$decimales,'.',',');
    }

    public function mesFecha($date)
    {
        $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        return $meses[date('n', strtotime($date))-1];
    }
}
?>