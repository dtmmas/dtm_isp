<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
            ->constrained()
            ->comment('quien reporta el pago');

            
            $table->foreignId('processed_by')
            ->nullable()
            ->constrained('users')
            ->comment('quien aprobo el pago');

            $table->foreignId('invoice_id')
            ->constrained();

            $table->string('no_voucher');
            $table->string('img_voucher')->nullable();
            $table->double('price', 12, 2);  
            $table->string('observation')->nullable();
            $table->enum('method_payment',['3','2','1'])->default('3')->comment('1->deposito, 2 ->paypal, 3->efectivo');
            $table->enum('status',['0','1','-1'])->default('0')->comment('0->pendiente, 1->aprobado, -1 ->rechazado');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
