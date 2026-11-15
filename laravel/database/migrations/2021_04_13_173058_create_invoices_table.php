<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_product_id')
            ->nullable()
            ->constrained('client_product')
            ->onDelete('SET null');


            $table->foreignId('user_id')
            ->nullable()
            ->constrained()
            ->onDelete('SET null')
            ->comment('quien crea la factura manualmente');

            $table->enum('status',['0','1','-1'])->default('0')->comment('0->pendiente, 1->pagada, -1 ->vencida');
            $table->double('price', 12, 2);            
            $table->timestamp('date_expiration')->nullable();
            $table->integer('payments_advancement')->default(0);
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
        Schema::dropIfExists('invoices');
    }
}
