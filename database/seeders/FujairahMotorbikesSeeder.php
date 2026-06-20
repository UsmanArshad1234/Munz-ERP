<?php

namespace Database\Seeders;

use App\Models\Motorbike;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FujairahMotorbikesSeeder extends Seeder
{
    public function run(): void
    {
        $bikes = [
            ['plate_number' => '96153', 'plate_code' => 'WHITE', 'year' => 2021, 'color' => 'BLACK',           'brand' => 'BAJAJ', 'model' => 'Pulsar',    'engine_number' => 'DHXCMA29756',    'chassis_number' => 'MD2A11CXXMCA12707', 'insurance_company' => 'Al Ain Ahlia Insurance Company',         'mulkiya_expiry' => '2027-05-21', 'insurance_expiry' => '2027-06-21'],
            ['plate_number' => '95931', 'plate_code' => 'WHITE', 'year' => 2022, 'color' => 'WHITE/BLACK',     'brand' => 'TVS',   'model' => 'Apache',    'engine_number' => 'DE7GN2500384',   'chassis_number' => 'MD637DE79N2G00464', 'insurance_company' => 'Al Ain Ahlia Insurance Company',         'mulkiya_expiry' => '2027-03-29', 'insurance_expiry' => '2027-04-29'],
            ['plate_number' => '95930', 'plate_code' => 'WHITE', 'year' => 2021, 'color' => 'BLACK',           'brand' => 'BAJAJ', 'model' => 'Pulsar',    'engine_number' => 'DHXCMF23938',    'chassis_number' => 'MD2A11CX0MCF13069', 'insurance_company' => 'Al Ain Ahlia Insurance Company',         'mulkiya_expiry' => '2027-03-29', 'insurance_expiry' => '2027-04-29'],
            ['plate_number' => '96152', 'plate_code' => 'WHITE', 'year' => 2021, 'color' => 'BLACK',           'brand' => 'BAJAJ', 'model' => 'Pulsar',    'engine_number' => 'DHXCMA24724',    'chassis_number' => 'MD2A11CX1MCA08738', 'insurance_company' => 'Al Ain Ahlia Insurance Company',         'mulkiya_expiry' => '2027-05-21', 'insurance_expiry' => '2027-06-21'],
            ['plate_number' => '95934', 'plate_code' => 'WHITE', 'year' => 2021, 'color' => 'BLACK',           'brand' => 'BAJAJ', 'model' => 'Pulsar',    'engine_number' => 'DHXCMC48848',    'chassis_number' => 'MD2A11CX7MCC24156', 'insurance_company' => 'Al Ain Ahlia Insurance Company',         'mulkiya_expiry' => '2027-03-29', 'insurance_expiry' => '2027-04-29'],
            ['plate_number' => '95932', 'plate_code' => 'WHITE', 'year' => 2021, 'color' => 'BLACK',           'brand' => 'BAJAJ', 'model' => 'Pulsar',    'engine_number' => 'DHXCMF23883',    'chassis_number' => 'MD2A11CX2MCF13073', 'insurance_company' => 'Al Ain Ahlia Insurance Company',         'mulkiya_expiry' => '2027-03-29', 'insurance_expiry' => '2027-04-29'],
            ['plate_number' => '97081', 'plate_code' => 'WHITE', 'year' => 2022, 'color' => 'BLACK',           'brand' => 'BAJAJ', 'model' => '150',        'engine_number' => 'DHXCMJ36917',    'chassis_number' => 'MD2A11CX3MCJ44639', 'insurance_company' => null,                                      'mulkiya_expiry' => '2027-01-04', 'insurance_expiry' => '2027-02-04'],
            ['plate_number' => '95937', 'plate_code' => 'WHITE', 'year' => 2021, 'color' => 'BLACK',           'brand' => 'BAJAJ', 'model' => 'Pulsar',    'engine_number' => 'DHXCMA29789',    'chassis_number' => 'MD2A11CX4MCA12721', 'insurance_company' => 'Al Ain Ahlia Insurance Company',         'mulkiya_expiry' => '2027-03-29', 'insurance_expiry' => '2027-04-29'],
            ['plate_number' => '95936', 'plate_code' => 'WHITE', 'year' => 2022, 'color' => 'WHITE/BLACK',     'brand' => 'TVS',   'model' => 'Apache',    'engine_number' => 'DE7FN2000252',   'chassis_number' => 'MD637DE72N2G00144', 'insurance_company' => 'Al Ain Ahlia Insurance Company',         'mulkiya_expiry' => '2027-03-29', 'insurance_expiry' => '2027-04-29'],
            ['plate_number' => '97082', 'plate_code' => 'WHITE', 'year' => 2022, 'color' => 'BLACK',           'brand' => 'BAJAJ', 'model' => '150',        'engine_number' => 'DHXCMJ36945',    'chassis_number' => 'MD2A11CX4MCJ44729', 'insurance_company' => null,                                      'mulkiya_expiry' => '2027-01-04', 'insurance_expiry' => '2027-02-04'],
            ['plate_number' => '96144', 'plate_code' => 'WHITE', 'year' => 2021, 'color' => 'BLACK',           'brand' => 'BAJAJ', 'model' => 'Pulsar',    'engine_number' => 'DHXCMF21178',    'chassis_number' => 'MD2A11CX2MCF12439', 'insurance_company' => 'Al Ain Ahlia Insurance Company',         'mulkiya_expiry' => '2027-06-16', 'insurance_expiry' => '2027-07-16'],
            ['plate_number' => '97094', 'plate_code' => 'WHITE', 'year' => 2025, 'color' => 'RED',             'brand' => 'HUAIHAI', 'model' => 'HHFL',    'engine_number' => 'GZ157FMJR8800024','chassis_number' => 'LA4YBCKCXS1000024', 'insurance_company' => 'Methaq Takaful Insurance Company',       'mulkiya_expiry' => '2026-11-04', 'insurance_expiry' => '2026-12-04'],
            ['plate_number' => '96154', 'plate_code' => 'WHITE', 'year' => 2021, 'color' => 'BLACK',           'brand' => 'BAJAJ', 'model' => 'Pulsar',    'engine_number' => 'DHXCMC48269',    'chassis_number' => 'MD2A11CX4MCC23515', 'insurance_company' => 'Al Ain Ahlia Insurance Company',         'mulkiya_expiry' => '2027-05-21', 'insurance_expiry' => '2027-06-21'],
            ['plate_number' => '97083', 'plate_code' => 'WHITE', 'year' => 2022, 'color' => 'BLACK',           'brand' => 'BAJAJ', 'model' => '150',        'engine_number' => 'DHXCNC14589',    'chassis_number' => 'MD2A11CX8NCC23535', 'insurance_company' => null,                                      'mulkiya_expiry' => '2027-01-04', 'insurance_expiry' => '2027-02-04'],
            ['plate_number' => '97437', 'plate_code' => 'WHITE', 'year' => 2025, 'color' => 'RED',             'brand' => 'HUAIHAI', 'model' => 'HHFL',    'engine_number' => 'GZ157FMJR8800018','chassis_number' => 'LA4YBCKC4S1000018', 'insurance_company' => 'Methaq Takaful Insurance Company',       'mulkiya_expiry' => '2026-11-04', 'insurance_expiry' => '2026-12-04'],
            ['plate_number' => '97084', 'plate_code' => 'WHITE', 'year' => 2022, 'color' => 'BLACK',           'brand' => 'BAJAJ', 'model' => '150',        'engine_number' => 'DHXCNB30341',    'chassis_number' => 'MD2A11CXXNCB70207', 'insurance_company' => null,                                      'mulkiya_expiry' => '2027-01-04', 'insurance_expiry' => '2027-02-04'],
            ['plate_number' => '97098', 'plate_code' => 'WHITE', 'year' => 2025, 'color' => 'RED',             'brand' => 'HUAIHAI', 'model' => 'HHFL',    'engine_number' => 'GZ157FMJR8800008','chassis_number' => 'LA4YBCKC1S1000008', 'insurance_company' => 'Methaq Takaful Insurance Company',       'mulkiya_expiry' => '2026-11-04', 'insurance_expiry' => '2026-12-04'],
            ['plate_number' => '97096', 'plate_code' => 'WHITE', 'year' => 2025, 'color' => 'RED',             'brand' => 'HUAIHAI', 'model' => 'HHFL',    'engine_number' => 'GZ157FMJR8800021','chassis_number' => 'LA4YBCKC4S1000021', 'insurance_company' => 'Methaq Takaful Insurance Company',       'mulkiya_expiry' => '2026-11-04', 'insurance_expiry' => '2026-12-04'],
            ['plate_number' => '97101', 'plate_code' => 'WHITE', 'year' => 2025, 'color' => 'RED',             'brand' => 'HUAIHAI', 'model' => 'HHFL',    'engine_number' => 'GZ157FMJR8800028','chassis_number' => 'LA4YBCKC7S1000028', 'insurance_company' => 'Methaq Takaful Insurance Company',       'mulkiya_expiry' => '2026-11-04', 'insurance_expiry' => '2026-12-04'],
            ['plate_number' => '97434', 'plate_code' => 'WHITE', 'year' => 2025, 'color' => 'RED',             'brand' => 'HUAIHAI', 'model' => 'HHFL',    'engine_number' => 'GZ157FMJR8800015','chassis_number' => 'LA4YBCKC9S1000015', 'insurance_company' => 'Methaq Takaful Insurance Company',       'mulkiya_expiry' => '2026-11-04', 'insurance_expiry' => '2026-12-04'],
            ['plate_number' => '97093', 'plate_code' => 'WHITE', 'year' => 2025, 'color' => 'RED',             'brand' => 'HUAIHAI', 'model' => 'HHFL',    'engine_number' => 'GZ157FMJR8800023','chassis_number' => 'LA4YBCKC8S1000023', 'insurance_company' => 'Methaq Takaful Insurance Company',       'mulkiya_expiry' => '2026-11-04', 'insurance_expiry' => '2026-12-04'],
            ['plate_number' => '97436', 'plate_code' => 'WHITE', 'year' => 2025, 'color' => 'RED',             'brand' => 'HUAIHAI', 'model' => 'HHFL',    'engine_number' => 'GZ157FMJR8800016','chassis_number' => 'LA4YBCKC0S1000016', 'insurance_company' => 'Methaq Takaful Insurance Company',       'mulkiya_expiry' => '2026-11-04', 'insurance_expiry' => '2026-12-04'],
            ['plate_number' => '97092', 'plate_code' => 'WHITE', 'year' => 2025, 'color' => 'RED',             'brand' => 'HUAIHAI', 'model' => 'HHFL',    'engine_number' => 'GZ157FMJR8800020','chassis_number' => 'LA4YBCKC2S1000020', 'insurance_company' => 'Methaq Takaful Insurance Company',       'mulkiya_expiry' => '2026-11-04', 'insurance_expiry' => '2026-12-04'],
            ['plate_number' => '97095', 'plate_code' => 'WHITE', 'year' => 2025, 'color' => 'RED',             'brand' => 'HUAIHAI', 'model' => 'HHFL',    'engine_number' => 'GZ157FMJR8800011','chassis_number' => 'LA4YBCKC1S1000011', 'insurance_company' => 'Methaq Takaful Insurance Company',       'mulkiya_expiry' => '2026-11-04', 'insurance_expiry' => '2026-12-04'],
            ['plate_number' => '97435', 'plate_code' => 'WHITE', 'year' => 2025, 'color' => 'RED',             'brand' => 'HUAIHAI', 'model' => 'HHFL',    'engine_number' => 'GZ157FMJR8800004','chassis_number' => 'LA4YBCKC4S1000004', 'insurance_company' => 'Methaq Takaful Insurance Company',       'mulkiya_expiry' => '2026-11-04', 'insurance_expiry' => '2026-12-04'],
            ['plate_number' => '97103', 'plate_code' => 'WHITE', 'year' => 2025, 'color' => 'RED',             'brand' => 'HUAIHAI', 'model' => 'HHFL',    'engine_number' => 'GZ157FMJR8800002','chassis_number' => 'LA4YBCKC0S1000002', 'insurance_company' => 'Methaq Takaful Insurance Company',       'mulkiya_expiry' => '2026-11-04', 'insurance_expiry' => '2026-12-04'],
            ['plate_number' => '97102', 'plate_code' => 'WHITE', 'year' => 2025, 'color' => 'RED',             'brand' => 'HUAIHAI', 'model' => 'HHFL',    'engine_number' => 'GZ157FMJR8800009','chassis_number' => 'LA4YBCKC3S1000009', 'insurance_company' => 'Methaq Takaful Insurance Company',       'mulkiya_expiry' => '2026-11-04', 'insurance_expiry' => '2026-12-04'],
        ];

        $existing = Motorbike::whereIn('chassis_number', array_column($bikes, 'chassis_number'))
            ->pluck('chassis_number')->toArray();

        $newBikes = array_filter($bikes, fn($b) => !in_array($b['chassis_number'], $existing));

        if (empty($newBikes)) {
            $this->command->info('FujairahMotorbikesSeeder: all bikes already exist, nothing to insert.');
            return;
        }

        $last = Motorbike::withTrashed()->orderByDesc('id')->value('bike_id');
        $num  = $last ? (int) substr($last, 3) : 0;

        DB::transaction(function () use ($newBikes, $num) {
            $i = 1;
            foreach ($newBikes as $bike) {
                Motorbike::create(array_merge($bike, [
                    'bike_id'  => 'BK-' . str_pad($num + $i, 4, '0', STR_PAD_LEFT),
                    'emirate'  => 'Fujairah',
                    'status'   => 'available',
                    'bike_type'=> 'Motorcycle',
                ]));
                $i++;
            }
        });

        $this->command->info('FujairahMotorbikesSeeder: ' . count($newBikes) . ' bikes inserted.');
    }
}
