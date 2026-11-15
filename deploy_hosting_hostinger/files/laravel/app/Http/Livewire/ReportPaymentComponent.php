<?php

namespace App\Http\Livewire;

use App\Models\Invoice;
use App\Models\Payment;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;

class ReportPaymentComponent extends Component
{
    use WithFileUploads;
    public $invoice;

    public $price;
    public $no_voucher;
    public $img_voucher;
    public $img_id;
    public $observation;

    protected function rules()
    {
        $rules = [
            'no_voucher' => 'required|unique:payments',
            'price' => 'required|numeric',
        ];

        $rules['img_voucher'] =  'image|max:4048';

        return $rules;
    }

    public function mount(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->price = $invoice->price;
        $this->img_id = rand();
    }

    public function render()
    {
        return view('livewire.report-payment-component');
    }

    public function resetInputFields()
    {
        $this->price = '';
        $this->no_voucher = '';
        $this->img_voucher = '';
        $this->observation = '';
    }

    public function store($tipo='1')
    {
        abort_if(!Auth::user()->can('payments.create'), 401);
        if($tipo=='1'){
            $this->validate();
        }

        $img = null;

        if($this->img_voucher){
            $method_payment = '1';
            $status=false;
            $img = $this->img_voucher->store('payments');
        }else{
            $method_payment = '3';
            $status=true;
            $this->observation='PAGO EN EFECTIVO';
            $this->no_voucher ='00000000000000';
        }

        $paymentCreated = $this->invoice->payments()->create([
            'no_voucher' => ($this->no_voucher!='')?$this->no_voucher:date('dHis'),
            'price' => $this->price,
            'img_voucher' =>  $img,
            'observation' => $this->observation,
            'method_payment' => $method_payment,
        ]);

        if($status){
            $payment = Payment::findOrFail($paymentCreated->id);
            $payment->status = '1';
            $payment->save();
        }
        $this->resetInputFields();
        $this->emitTo('invoice-component','onRefresh');
        $this->emit('paymentUpdateStore', 'Pago reportado correctamente.', $this->invoice->id, $paymentCreated->id); // Close modal and trigger print
    }
}
