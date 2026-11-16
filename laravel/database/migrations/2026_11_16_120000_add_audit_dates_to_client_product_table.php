<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddAuditDatesToClientProductTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('client_product', 'suspended_at')) {
            Schema::table('client_product', function (Blueprint $table) {
                $table->timestamp('suspended_at')->nullable()->after('status');
            });
        }

        if (!Schema::hasColumn('client_product', 'reactivated_at')) {
            Schema::table('client_product', function (Blueprint $table) {
                $table->timestamp('reactivated_at')->nullable()->after('suspended_at');
            });
        }

        if (Schema::hasColumn('client_product', 'reactivated_at')) {
            DB::table('client_product')
                ->where('status', '1')
                ->whereNull('reactivated_at')
                ->update([
                    'reactivated_at' => DB::raw('COALESCE(date_instalation, created_at)'),
                ]);
        }
    }

    public function down()
    {
        if (Schema::hasColumn('client_product', 'reactivated_at')) {
            Schema::table('client_product', function (Blueprint $table) {
                $table->dropColumn('reactivated_at');
            });
        }

        if (Schema::hasColumn('client_product', 'suspended_at')) {
            Schema::table('client_product', function (Blueprint $table) {
                $table->dropColumn('suspended_at');
            });
        }
    }
}
