<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Traits\FunctionGeneral;

class PaymentObserver
{
    use FunctionGeneral;

    public function creating(Payment $payment)
    {
        if(! \App::runningInConsole()){
            $payment->user_id = Auth()->user()->id;
        }
    }
    
    public function updating(Payment $payment)
    {
        if(! \App::runningInConsole()){
            $payment->processed_by = Auth()->user()->id;
        }
    }

    public function updated(Payment $payment)
    {
        if($payment->status=='1'){
            $invoice = Invoice::findOrFail($payment->invoice_id);
            $invoice->update(['status'=>'1']);
        }else{
            $invoice = Invoice::findOrFail($payment->invoice_id);
            if($invoice->date_expiration>$this->fechaCortaActual()){
                $invoice->update(['status'=>'0']);
            }else{
                $invoice->update(['status'=>'-1']);
            }
        }
    }
}
