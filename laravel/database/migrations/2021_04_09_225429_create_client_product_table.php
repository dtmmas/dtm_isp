<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_product', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')
            ->constrained()
            ->onDelete('cascade');

            $table->foreignId('product_id')
            ->constrained()
            ->onDelete('cascade');

            $table->foreignId('node_id')
            ->nullable()
            ->constrained()
            ->onDelete('SET null');

            $table->date('date_instalation');
            $table->string('address');
            $table->string('reference');
            $table->double('amount_installation',12,2)->default(0);
            $table->enum('advance',['0','1'])->default('0')->comment('1-> pago anticipado');
            $table->enum('status',['-1','0','1'])->default('1')->comment('1-> activo, 0-> suspendido, -1 -> cancelado');
            $table->integer('payments_advancement')->default(0);
            $table->date('discounted_month')->nullable()->comment('ultimo mes descontado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
