<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMapInfrastructureTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('map_assets')) {
            Schema::create('map_assets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('zone_id')->nullable();
                $table->unsignedBigInteger('node_id')->nullable();
                $table->string('name', 191);
                $table->string('code', 80)->nullable();
                $table->string('asset_type', 40);
                $table->string('asset_status', 30)->default('activo');
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->text('description')->nullable();
                $table->text('meta')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('map_fiber_routes')) {
            Schema::create('map_fiber_routes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('zone_id')->nullable();
                $table->unsignedBigInteger('node_id')->nullable();
                $table->string('name', 191);
                $table->string('route_type', 40)->default('fibra');
                $table->string('stroke_color', 20)->nullable();
                $table->unsignedInteger('stroke_weight')->default(4);
                $table->longText('path_points');
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('map_coverages')) {
            Schema::create('map_coverages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('zone_id')->nullable();
                $table->unsignedBigInteger('node_id')->nullable();
                $table->string('name', 191);
                $table->string('coverage_type', 30)->default('circle');
                $table->string('technology', 50)->nullable();
                $table->decimal('center_latitude', 10, 7);
                $table->decimal('center_longitude', 10, 7);
                $table->unsignedInteger('radius_meters')->nullable();
                $table->longText('polygon_points')->nullable();
                $table->string('fill_color', 20)->nullable();
                $table->string('stroke_color', 20)->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        $this->seedExampleInfrastructure();
    }

    public function down()
    {
        Schema::dropIfExists('map_coverages');
        Schema::dropIfExists('map_fiber_routes');
        Schema::dropIfExists('map_assets');
    }

    private function seedExampleInfrastructure()
    {
        $zoneId = DB::table('zones')->orderBy('id')->value('id');
        $nodeId = DB::table('nodes')->orderBy('id')->value('id');

        if (!$zoneId && !$nodeId) {
            return;
        }

        if (DB::table('map_assets')->count() === 0) {
            DB::table('map_assets')->insert([
                [
                    'zone_id' => $zoneId,
                    'node_id' => $nodeId,
                    'name' => 'Antena principal Jacaltenango',
                    'code' => 'AP-01',
                    'asset_type' => 'antena',
                    'asset_status' => 'activo',
                    'latitude' => 15.3138000,
                    'longitude' => -91.4784000,
                    'description' => 'Punto principal de distribucion inalambrica.',
                    'meta' => json_encode(['modelo' => 'Rocket Prism', 'sector' => '120 grados']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'zone_id' => $zoneId,
                    'node_id' => $nodeId,
                    'name' => 'OLT central',
                    'code' => 'OLT-01',
                    'asset_type' => 'olt',
                    'asset_status' => 'activo',
                    'latitude' => 15.3132000,
                    'longitude' => -91.4779000,
                    'description' => 'OLT principal para clientes FTTH.',
                    'meta' => json_encode(['puertos' => 16, 'tecnologia' => 'GPON']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'zone_id' => $zoneId,
                    'node_id' => $nodeId,
                    'name' => 'NAP 8 puertos centro',
                    'code' => 'NAP-01',
                    'asset_type' => 'nap',
                    'asset_status' => 'activo',
                    'latitude' => 15.3144000,
                    'longitude' => -91.4789000,
                    'description' => 'Caja NAP para distribucion en calle principal.',
                    'meta' => json_encode(['capacidad' => 8]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'zone_id' => $zoneId,
                    'node_id' => $nodeId,
                    'name' => 'Poste P-14',
                    'code' => 'POSTE-14',
                    'asset_type' => 'poste',
                    'asset_status' => 'activo',
                    'latitude' => 15.3149500,
                    'longitude' => -91.4792000,
                    'description' => 'Poste de soporte para fibra y energia.',
                    'meta' => json_encode(['material' => 'concreto']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        if (DB::table('map_fiber_routes')->count() === 0) {
            DB::table('map_fiber_routes')->insert([
                'zone_id' => $zoneId,
                'node_id' => $nodeId,
                'name' => 'Troncal fibra centro',
                'route_type' => 'fibra',
                'stroke_color' => '#0ea5e9',
                'stroke_weight' => 4,
                'path_points' => json_encode([
                    ['lat' => 15.3132000, 'lng' => -91.4779000],
                    ['lat' => 15.3137000, 'lng' => -91.4781000],
                    ['lat' => 15.3144000, 'lng' => -91.4789000],
                    ['lat' => 15.3149500, 'lng' => -91.4792000],
                ]),
                'description' => 'Ruta principal de fibra desde la OLT hacia la caja NAP y poste.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (DB::table('map_coverages')->count() === 0) {
            DB::table('map_coverages')->insert([
                'zone_id' => $zoneId,
                'node_id' => $nodeId,
                'name' => 'Cobertura AP centro',
                'coverage_type' => 'circle',
                'technology' => 'Wireless 5 GHz',
                'center_latitude' => 15.3138000,
                'center_longitude' => -91.4784000,
                'radius_meters' => 550,
                'polygon_points' => null,
                'fill_color' => '#22c55e',
                'stroke_color' => '#15803d',
                'description' => 'Cobertura estimada del AP principal.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
