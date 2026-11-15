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
        Schema::table('client_product', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('reference');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->text('installation_photos')->nullable()->after('payments_advancement');
        });
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
