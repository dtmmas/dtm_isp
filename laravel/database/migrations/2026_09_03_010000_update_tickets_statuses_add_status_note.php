<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTicketsStatusesAddStatusNote extends Migration
{
    public function up()
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'status_note')) {
                $table->text('status_note')->nullable()->after('status');
            }
        });

        DB::table('tickets')
            ->where('status', 'abierto')
            ->update(['status' => 'pendiente']);

        DB::table('tickets')
            ->where('status', 'en_proceso')
            ->update(['status' => 'pendiente']);

        DB::table('tickets')
            ->where('status', 'cerrado')
            ->update(['status' => 'resuelto']);
    }

    public function down()
    {
        DB::table('tickets')
            ->where('status', 'pendiente')
            ->update(['status' => 'abierto']);

        DB::table('tickets')
            ->where('status', 'resuelto')
            ->update(['status' => 'cerrado']);

        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'status_note')) {
                $table->dropColumn('status_note');
            }
        });
    }
}
