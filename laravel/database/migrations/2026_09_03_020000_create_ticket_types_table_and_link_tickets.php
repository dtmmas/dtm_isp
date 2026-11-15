<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTicketTypesTableAndLinkTickets extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('ticket_types')) {
            Schema::create('ticket_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 191)->unique();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasColumn('tickets', 'ticket_type_id')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->unsignedBigInteger('ticket_type_id')->nullable()->after('user_id');
            });
        }

        $existingNames = DB::table('tickets')
            ->whereNull('deleted_at')
            ->whereNotNull('title')
            ->select('title')
            ->distinct()
            ->pluck('title');

        foreach ($existingNames as $title) {
            $name = trim((string) $title);

            if ($name === '') {
                continue;
            }

            DB::table('ticket_types')->updateOrInsert(
                ['name' => $name],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }

        $generalTypeId = DB::table('ticket_types')->where('name', 'General')->value('id');

        if (!$generalTypeId) {
            $generalTypeId = DB::table('ticket_types')->insertGetId([
                'name' => 'General',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $tickets = DB::table('tickets')
            ->select('id', 'title')
            ->whereNull('ticket_type_id')
            ->get();

        foreach ($tickets as $ticket) {
            $name = trim((string) $ticket->title);
            $typeId = $name !== ''
                ? DB::table('ticket_types')->where('name', $name)->value('id')
                : $generalTypeId;

            DB::table('tickets')
                ->where('id', $ticket->id)
                ->update(['ticket_type_id' => $typeId ?: $generalTypeId]);
        }

        DB::table('tickets')
            ->whereNull('ticket_type_id')
            ->update(['ticket_type_id' => $generalTypeId]);
    }

    public function down()
    {
        if (Schema::hasColumn('tickets', 'ticket_type_id')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('ticket_type_id');
            });
        }

        Schema::dropIfExists('ticket_types');
    }
}
