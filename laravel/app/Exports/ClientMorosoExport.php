<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClientMorosoExport implements FromView, ShouldQueue
{
    private $data;
     
    public function __construct( $data) {
        $this->data = $data;
    }

    public function view(): View {
        return view('report.export.clients-morosos', ['list_client_products' => $this->data]);
    }

    // /**
    // * @return \Illuminate\Support\Collection
    // */
    // public function collection()
    // {
    //     return $this->data;
    // }
}