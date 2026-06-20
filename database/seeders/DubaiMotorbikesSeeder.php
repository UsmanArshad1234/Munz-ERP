<?php

namespace Database\Seeders;

use App\Models\Motorbike;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DubaiMotorbikesSeeder extends Seeder
{
    public function run(): void
    {
        $bikes = [
            ['plate_number' => '24132', 'plate_code' => '9', 'year' => 2026, 'color' => 'RED',   'brand' => 'HERO',    'model' => 'Hunk',        'engine_number' => 'KC13EKSGL00721',   'chassis_number' => 'MBLKCS268TGZ00430', 'insurance_company' => 'AMG Insurance Company', 'mulkiya_expiry' => '2027-04-29', 'insurance_expiry' => '2027-05-29'],
            ['plate_number' => '85705', 'plate_code' => '2', 'year' => 2025, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => 'Pulsar 150',  'engine_number' => 'DHXCSD22876',      'chassis_number' => 'MD2A11CX1SCD57914', 'insurance_company' => 'AMG Insurance Company', 'mulkiya_expiry' => '2026-08-27', 'insurance_expiry' => '2026-09-27'],
            ['plate_number' => '85706', 'plate_code' => '2', 'year' => 2025, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => 'Pulsar 150',  'engine_number' => 'DHXCSD22890',      'chassis_number' => 'MD2A11CX7SCD57951', 'insurance_company' => 'AMG Insurance Company', 'mulkiya_expiry' => '2026-08-27', 'insurance_expiry' => '2026-09-27'],
            ['plate_number' => '95530', 'plate_code' => '2', 'year' => 2025, 'color' => 'RED',   'brand' => 'HUAIHAI', 'model' => 'HHFL',        'engine_number' => 'GZ157FMJR8800001', 'chassis_number' => 'LA4YBCKC9S1000001', 'insurance_company' => 'AMG Insurance Company', 'mulkiya_expiry' => '2026-10-21', 'insurance_expiry' => '2026-11-21'],
            ['plate_number' => '24106', 'plate_code' => '9', 'year' => 2026, 'color' => 'RED',   'brand' => 'HERO',    'model' => 'Hunk',        'engine_number' => 'KC13EKSGM00433',   'chassis_number' => 'MBLKCS267TGZ00502', 'insurance_company' => 'AMG Insurance Company', 'mulkiya_expiry' => '2027-04-29', 'insurance_expiry' => '2027-05-29'],
            ['plate_number' => '24138', 'plate_code' => '9', 'year' => 2026, 'color' => 'RED',   'brand' => 'HERO',    'model' => 'Hunk',        'engine_number' => 'NIL',              'chassis_number' => 'MBLKCS264TGZ00313', 'insurance_company' => 'AMG Insurance Company', 'mulkiya_expiry' => '2027-04-29', 'insurance_expiry' => '2027-05-29'],
            ['plate_number' => '24148', 'plate_code' => '9', 'year' => 2026, 'color' => 'RED',   'brand' => 'HERO',    'model' => 'Hunk',        'engine_number' => 'KC13EKSGM00334',   'chassis_number' => 'MBLKCS266TGZ00328', 'insurance_company' => 'AMG Insurance Company', 'mulkiya_expiry' => '2027-04-29', 'insurance_expiry' => '2027-05-29'],
            ['plate_number' => '24125', 'plate_code' => '9', 'year' => 2026, 'color' => 'RED',   'brand' => 'HERO',    'model' => 'Hunk',        'engine_number' => 'KC13EKSGM00310',   'chassis_number' => 'MBLKCS265TGZ00322', 'insurance_company' => 'AMG Insurance Company', 'mulkiya_expiry' => '2027-04-29', 'insurance_expiry' => '2027-05-29'],
            ['plate_number' => '96162', 'plate_code' => '2', 'year' => 2025, 'color' => 'RED',   'brand' => 'HUAIHAI', 'model' => 'HHFL',        'engine_number' => 'GZ157FMJR8800012', 'chassis_number' => 'LA4YBCKC3S1000012', 'insurance_company' => 'AMG Insurance Company', 'mulkiya_expiry' => '2026-10-21', 'insurance_expiry' => '2026-11-21'],
            ['plate_number' => '85704', 'plate_code' => '2', 'year' => 2025, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => 'Pulsar 150',  'engine_number' => 'DHXCSD22723',      'chassis_number' => 'MD2A11CX3SCD57834', 'insurance_company' => 'AMG Insurance Company', 'mulkiya_expiry' => '2026-08-27', 'insurance_expiry' => '2026-09-27'],
            ['plate_number' => '85710', 'plate_code' => '2', 'year' => 2025, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => 'Pulsar 150',  'engine_number' => 'DHXCSD22782',      'chassis_number' => 'MD2A11CX1SCD57945', 'insurance_company' => 'AMG Insurance Company', 'mulkiya_expiry' => '2026-08-27', 'insurance_expiry' => '2026-09-27'],
            ['plate_number' => '82980', 'plate_code' => '2', 'year' => 2025, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => 'Avenger',     'engine_number' => 'PDXCSE12791',      'chassis_number' => 'MD2A22EX7SCE71870', 'insurance_company' => 'AMG Insurance Company', 'mulkiya_expiry' => '2026-08-27', 'insurance_expiry' => '2026-09-27'],
            ['plate_number' => '85709', 'plate_code' => '2', 'year' => 2025, 'color' => 'BLACK', 'brand' => 'BAJAJ',   'model' => 'Pulsar 150',  'engine_number' => 'DHXCSD22862',      'chassis_number' => 'MD2A11CX5SCD57933', 'insurance_company' => 'AMG Insurance Company', 'mulkiya_expiry' => '2026-08-27', 'insurance_expiry' => '2026-09-27'],
        ];

        $existing = Motorbike::whereIn('chassis_number', array_column($bikes, 'chassis_number'))
            ->pluck('chassis_number')->toArray();

        $newBikes = array_filter($bikes, fn($b) => !in_array($b['chassis_number'], $existing));

        if (empty($newBikes)) {
            $this->command->info('DubaiMotorbikesSeeder: all bikes already exist, nothing to insert.');
            return;
        }

        $last = Motorbike::withTrashed()->orderByDesc('id')->value('bike_id');
        $num  = $last ? (int) substr($last, 3) : 0;

        DB::transaction(function () use ($newBikes, $num) {
            $i = 1;
            foreach ($newBikes as $bike) {
                Motorbike::create(array_merge($bike, [
                    'bike_id'  => 'BK-' . str_pad($num + $i, 4, '0', STR_PAD_LEFT),
                    'emirate'  => 'Dubai',
                    'status'   => 'available',
                    'bike_type'=> 'Motorcycle',
                ]));
                $i++;
            }
        });

        $this->command->info('DubaiMotorbikesSeeder: ' . count($newBikes) . ' bikes inserted.');
    }
}
