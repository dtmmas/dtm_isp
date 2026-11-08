<?php
namespace App\Http\Controllers;
use App\Traits\FunctionGeneral;
use App\Models\Payment;
use PDF;

class PdfController extends Controller
{
    use FunctionGeneral;

    public function payment($payment_id)
    {
        $payment = Payment::with('invoice.client_product.client.user', 'user')
        ->find($payment_id);
        $mes = $this->mesFecha($payment->invoice->date);
        $nombre = 'Factura_'.$payment->numero.'.pdf';
        $pdf = PDF::loadView('pdfs.payment', compact('payment','mes'))->setPaper("A6", "portrait");
        return $pdf->stream($nombre);
    }

    public function paymentPrint($payment_id)
    {
        $payment = Payment::with('invoice.client_product.client.user', 'user')
        ->findOrFail($payment_id);
        $mes = $this->mesFecha($payment->invoice->date);

        return view('payments.print', compact('payment', 'mes'));
    }
}
