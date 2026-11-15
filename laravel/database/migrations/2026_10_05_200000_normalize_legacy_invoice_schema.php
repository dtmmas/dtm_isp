<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NormalizeLegacyInvoiceSchema extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('invoices', 'user_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('client_product_id');
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('invoices', 'payments_advancement')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->integer('payments_advancement')->default(0)->after('date_expiration');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('invoices', 'user_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }

        if (Schema::hasColumn('invoices', 'payments_advancement')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('payments_advancement');
            });
        }
    }
}
