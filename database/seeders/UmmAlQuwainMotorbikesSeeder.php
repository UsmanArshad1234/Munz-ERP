<?php

namespace Database\Seeders;

use App\Models\Motorbike;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UmmAlQuwainMotorbikesSeeder extends Seeder
{
    public function run(): void
    {
        $bikes = [
            ['plate_number' => '8305', 'plate_code' => 'A', 'year' => 2025, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => 'C 150',   'engine_number' => 'DHXCSD22621',    'chassis_number' => 'MD2A11CX7SCD57805', 'insurance_company' => 'Al Ain Ahlia Insurance Company', 'mulkiya_expiry' => '2027-03-03', 'insurance_expiry' => '2027-04-03'],
            ['plate_number' => '8390', 'plate_code' => 'A', 'year' => 2022, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => 'Pulsar',  'engine_number' => 'DHXCND27449',    'chassis_number' => 'MD2A11CX9NCD35647', 'insurance_company' => 'Dubai Insurance Company',        'mulkiya_expiry' => '2027-01-05', 'insurance_expiry' => '2027-02-05'],
            ['plate_number' => '8389', 'plate_code' => 'A', 'year' => 2022, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => 'Pulsar',  'engine_number' => 'DHXCNC47914',    'chassis_number' => 'MD2A11CX3NCC14130', 'insurance_company' => 'Dubai Insurance Company',        'mulkiya_expiry' => '2027-01-05', 'insurance_expiry' => '2027-02-05'],
            ['plate_number' => '8364', 'plate_code' => 'A', 'year' => 2025, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => 'Pulsar',  'engine_number' => 'DHXCSD22784',    'chassis_number' => 'MD2A11CXXSCD57944', 'insurance_company' => 'Al Ain Ahlia Insurance Company', 'mulkiya_expiry' => '2027-01-19', 'insurance_expiry' => '2027-02-19'],
            ['plate_number' => '8309', 'plate_code' => 'A', 'year' => 2025, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => 'C 150',   'engine_number' => 'DHXCSD22799',    'chassis_number' => 'MD2A11CX3SCD57929', 'insurance_company' => 'Al Ain Ahlia Insurance Company', 'mulkiya_expiry' => '2027-03-03', 'insurance_expiry' => '2027-04-03'],
            ['plate_number' => '8304', 'plate_code' => 'A', 'year' => 2025, 'color' => 'RED',   'brand' => 'HUAIHAI', 'model' => 'HHFL',    'engine_number' => 'GZ157FMJR8800030','chassis_number' => 'LA4YBCKC5S1000030', 'insurance_company' => 'Al Ain Ahlia Insurance Company', 'mulkiya_expiry' => '2027-03-03', 'insurance_expiry' => '2027-04-03'],
            ['plate_number' => '8378', 'plate_code' => 'A', 'year' => 2022, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => 'Pulsar',  'engine_number' => 'DHXCND27510',    'chassis_number' => 'MD2A11CX4NCD35622', 'insurance_company' => 'Dubai Insurance Company',        'mulkiya_expiry' => '2027-01-05', 'insurance_expiry' => '2027-02-05'],
            ['plate_number' => '8365', 'plate_code' => 'A', 'year' => 2025, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => 'C 150',   'engine_number' => 'DHXCSD22638',    'chassis_number' => 'MD2A11CXXSCD57801', 'insurance_company' => 'Al Ain Ahlia Insurance Company', 'mulkiya_expiry' => '2027-01-19', 'insurance_expiry' => '2027-02-19'],
            ['plate_number' => '8379', 'plate_code' => 'A', 'year' => 2022, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => 'Pulsar',  'engine_number' => 'DHXCNC07152',    'chassis_number' => 'MD2A11CX9NCC17372', 'insurance_company' => 'Dubai Insurance Company',        'mulkiya_expiry' => '2027-01-05', 'insurance_expiry' => '2027-02-05'],
            ['plate_number' => '8382', 'plate_code' => 'A', 'year' => 2022, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => 'Pulsar',  'engine_number' => 'DHXCNC47971',    'chassis_number' => 'MD2A11CX8NCC14107', 'insurance_company' => 'Dubai Insurance Company',        'mulkiya_expiry' => '2027-01-05', 'insurance_expiry' => '2027-02-05'],
            ['plate_number' => '8306', 'plate_code' => 'A', 'year' => 2025, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => 'C 150',   'engine_number' => 'DHXCSD22636',    'chassis_number' => 'MD2A11CX9SCD57837', 'insurance_company' => 'Al Ain Ahlia Insurance Company', 'mulkiya_expiry' => '2027-03-03', 'insurance_expiry' => '2027-04-03'],
            ['plate_number' => '8381', 'plate_code' => 'A', 'year' => 2022, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => 'Pulsar',  'engine_number' => 'DHXCNC14550',    'chassis_number' => 'MD2A11CX8NCC23549', 'insurance_company' => 'Dubai Insurance Company',        'mulkiya_expiry' => '2027-01-05', 'insurance_expiry' => '2027-02-05'],
            ['plate_number' => '8387', 'plate_code' => 'A', 'year' => 2022, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => '150',     'engine_number' => 'DHXCNB30325',    'chassis_number' => 'MD2A11CX9NCB70215', 'insurance_company' => 'Dubai Insurance Company',        'mulkiya_expiry' => '2027-01-05', 'insurance_expiry' => '2027-02-05'],
            ['plate_number' => '8385', 'plate_code' => 'A', 'year' => 2022, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => 'Pulsar',  'engine_number' => 'DHXCMJ36851',    'chassis_number' => 'MD2A11CX3MCJ44124', 'insurance_company' => 'Dubai Insurance Company',        'mulkiya_expiry' => '2027-01-05', 'insurance_expiry' => '2027-02-05'],
            ['plate_number' => '8375', 'plate_code' => 'A', 'year' => 2022, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => 'Pulsar',  'engine_number' => 'DHXCNC05970',    'chassis_number' => 'MD2A11CX0NCC17390', 'insurance_company' => 'Dubai Insurance Company',        'mulkiya_expiry' => '2027-01-05', 'insurance_expiry' => '2027-02-05'],
            ['plate_number' => '8386', 'plate_code' => 'A', 'year' => 2022, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => '150',     'engine_number' => 'DHXCNB30364',    'chassis_number' => 'MD2A11CX4NCB70204', 'insurance_company' => 'Dubai Insurance Company',        'mulkiya_expiry' => '2027-01-05', 'insurance_expiry' => '2027-02-05'],
            ['plate_number' => '8307', 'plate_code' => 'A', 'year' => 2025, 'color' => 'RED',   'brand' => 'HUAIHAI', 'model' => 'HHFL',    'engine_number' => 'GZ157FMJR8800027','chassis_number' => 'LA4YBCKC5S1000027', 'insurance_company' => 'Al Ain Ahlia Insurance Company', 'mulkiya_expiry' => '2027-03-03', 'insurance_expiry' => '2027-04-03'],
            ['plate_number' => '8374', 'plate_code' => 'A', 'year' => 2022, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => 'Pulsar',  'engine_number' => 'DHXCNC05997',    'chassis_number' => 'MD2A11CXXNCC17378', 'insurance_company' => 'Dubai Insurance Company',        'mulkiya_expiry' => '2027-01-05', 'insurance_expiry' => '2027-02-05'],
            ['plate_number' => '8362', 'plate_code' => 'A', 'year' => 2025, 'color' => 'RED',   'brand' => 'HUAIHAI', 'model' => 'HHFL',    'engine_number' => 'GZ157FMJR8800029','chassis_number' => 'LA4YBCKC9S1000029', 'insurance_company' => 'Al Ain Ahlia Insurance Company', 'mulkiya_expiry' => '2027-01-19', 'insurance_expiry' => '2027-02-19'],
            ['plate_number' => '8376', 'plate_code' => 'A', 'year' => 2022, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => 'Pulsar',  'engine_number' => 'DHXCNC14546',    'chassis_number' => 'MD2A11CX7NCC23591', 'insurance_company' => 'Dubai Insurance Company',        'mulkiya_expiry' => '2027-01-05', 'insurance_expiry' => '2027-02-05'],
        ];

        $existing = Motorbike::whereIn('chassis_number', array_column($bikes, 'chassis_number'))
            ->pluck('chassis_number')->toArray();

        $newBikes = array_filter($bikes, fn($b) => !in_array($b['chassis_number'], $existing));

        if (empty($newBikes)) {
            $this->command->info('UmmAlQuwainMotorbikesSeeder: all bikes already exist, nothing to insert.');
            return;
        }

        $last = Motorbike::withTrashed()->orderByDesc('id')->value('bike_id');
        $num  = $last ? (int) substr($last, 3) : 0;

        DB::transaction(function () use ($newBikes, $num) {
            $i = 1;
            foreach ($newBikes as $bike) {
                Motorbike::create(array_merge($bike, [
                    'bike_id'  => 'BK-' . str_pad($num + $i, 4, '0', STR_PAD_LEFT),
                    'emirate'  => 'Umm Al Quwain',
                    'status'   => 'available',
                    'bike_type'=> 'Motorcycle',
                ]));
                $i++;
            }
        });

        $this->command->info('UmmAlQuwainMotorbikesSeeder: ' . count($newBikes) . ' bikes inserted.');
    }
}
