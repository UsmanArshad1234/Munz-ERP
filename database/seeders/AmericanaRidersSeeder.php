<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AmericanaRidersSeeder extends Seeder
{
    public function run(): void
    {
        $riders = [
            ['name' => 'Muhammad Sameer',   'platform_id' => '306011', 'work_emirate' => 'Khor Fakkan', 'notes' => 'KFC - Khorfakkan'],
            ['name' => 'Shehran Ussain',    'platform_id' => '306664', 'work_emirate' => 'Khor Fakkan', 'notes' => 'KFC - Khorfakkan'],
            ['name' => 'Mohmmed Talimand',  'platform_id' => '306770', 'work_emirate' => 'Khor Fakkan', 'notes' => 'KFC - Khorfakkan'],
            ['name' => 'Williams AckaH',    'platform_id' => '306823', 'work_emirate' => 'Khor Fakkan', 'notes' => 'KFC - Khorfakkan'],
            ['name' => 'Ibrahim Moro',      'platform_id' => '306824', 'work_emirate' => 'Khor Fakkan', 'notes' => 'KFC - Khorfakkan'],
            ['name' => 'Zaman',             'platform_id' => '306056', 'work_emirate' => 'Fujairah',    'notes' => 'KFC - Dibba'],
            ['name' => 'Abdulla',           'platform_id' => '306326', 'work_emirate' => 'Khor Fakkan', 'notes' => 'KFC - Soor Kalba'],
            ['name' => 'Mohammed Salim',    'platform_id' => '306517', 'work_emirate' => 'Fujairah',    'notes' => 'KFC - Emarat Dibba Al Ras'],
            ['name' => 'Fahad Riaz',        'platform_id' => '306793', 'work_emirate' => 'Fujairah',    'notes' => 'KFC - Dibba'],
            ['name' => 'Bright Aggrey',     'platform_id' => '306829', 'work_emirate' => 'Khor Fakkan', 'notes' => 'Hardees - Khorfakkan New'],
        ];

        // Skip riders whose platform_id already exists
        $existing = Employee::whereIn('platform_id', array_column($riders, 'platform_id'))
            ->pluck('platform_id')
            ->toArray();

        $newRiders = array_filter($riders, fn($r) => !in_array($r['platform_id'], $existing));

        if (empty($newRiders)) {
            $this->command->info('AmericanaRidersSeeder: all riders already exist, nothing to insert.');
            return;
        }

        // Generate next Employee IDs
        $last = Employee::withTrashed()->orderByDesc('id')->value('employee_id');
        $num  = $last ? (int) substr($last, 4) : 0;

        DB::transaction(function () use ($newRiders, $num) {
            $i = 1;
            foreach ($newRiders as $rider) {
                $empId = 'EMP-' . str_pad($num + $i, 4, '0', STR_PAD_LEFT);
                Employee::create([
                    'employee_id'   => $empId,
                    'name'          => $rider['name'],
                    'platform_id'   => $rider['platform_id'],
                    'platform_name' => 'Americana',
                    'work_emirate'  => $rider['work_emirate'],
                    'notes'         => $rider['notes'],
                    'job_title'     => 'Rider',
                    'status'        => 'active',
                ]);
                $i++;
            }
        });

        $this->command->info('AmericanaRidersSeeder: ' . count($newRiders) . ' riders inserted.');
    }
}
