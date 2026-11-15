<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationAndPhotosToClientProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('client_product', 'payments_advancement')) {
            Schema::table('client_product', function (Blueprint $table) {
                $table->integer('payments_advancement')->default(0)->after('status');
            });
        }

        if (!Schema::hasColumn('client_product', 'discounted_month')) {
            Schema::table('client_product', function (Blueprint $table) {
                $table->date('discounted_month')->nullable()->after('payments_advancement');
            });
        }

        if (!Schema::hasColumn('client_product', 'latitude')) {
            Schema::table('client_product', function (Blueprint $table) {
                $table->decimal('latitude', 10, 7)->nullable()->after('reference');
            });
        }

        if (!Schema::hasColumn('client_product', 'longitude')) {
            Schema::table('client_product', function (Blueprint $table) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            });
        }

        if (!Schema::hasColumn('client_product', 'installation_photos')) {
            Schema::table('client_product', function (Blueprint $table) {
                $table->text('installation_photos')->nullable()->after('discounted_month');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_product', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'installation_photos']);
        });
    }
}
