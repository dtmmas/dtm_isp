<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use \Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use Livewire\WithFileUploads;


class ClientImportarComponent extends Component
{
    use WithFileUploads;
    public $archivo;
    public $archivo_id;
    public $validado = false;
    public $cargando = false;
    public $finalizado = false;
    //variables de nuestro modelo

    public function mount()
    {
        $this->archivo_id = uniqid();
    }

    public function render()
    {   
        return view('livewire.client-importar-component');
    }

    public function ValidateClient()
    {   
        session()->forget('validacionExcel');
        $this->validate([
            'archivo' => ['required','mimes:xlsx'],
        ]);
        Excel::import(new \App\Imports\ClientImport, $this->archivo);
        $data = session()->get('validacionExcel');
        if(!$data['rps']){
            session()->forget('validacionExcel');
        }
        $this->emit('ValidateClient',$data);
    }

    public function ImportarClient()
    {
        $this->validado = true;
        $this->validate([
            'archivo' => ['required', ],
        ]);
        Excel::import(new \App\Imports\ClientImport, $this->archivo);
        session()->forget('validacionExcel');
        $this->emit('ImportarClientImport',session()->get('importarUser'));
    }
    //listeners / escuchar eventos js o php y ejecutar acciones livewire
    protected $listeners = [
        'onImportData' =>'ImportarClient'
    ];


}
