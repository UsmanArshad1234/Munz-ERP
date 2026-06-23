<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const MAP = [
        'dubai'          => 'Dubai',
        'abu dhabi'      => 'Abu Dhabi',
        'abu_dhabi'      => 'Abu Dhabi',
        'abudhabi'       => 'Abu Dhabi',
        'sharjah'        => 'Sharjah',
        'ajman'          => 'Ajman',
        'fujairah'       => 'Fujairah',
        'ras al khaimah' => 'Ras Al Khaimah',
        'ras_al_khaimah' => 'Ras Al Khaimah',
        'rasalkhaimah'   => 'Ras Al Khaimah',
        'rak'            => 'Ras Al Khaimah',
        'umm al quwain'  => 'Umm Al Quwain',
        'umm_al_quwain'  => 'Umm Al Quwain',
        'ummalquwain'    => 'Umm Al Quwain',
        'uaq'            => 'Umm Al Quwain',
        'al ain'         => 'Al Ain',
        'al_ain'         => 'Al Ain',
        'alain'          => 'Al Ain',
        'khor fakkan'    => 'Khor Fakkan',
        'khorfakkan'     => 'Khor Fakkan',
        'khor_fakkan'    => 'Khor Fakkan',
        'other'          => 'Other',
    ];

    public function up(): void
    {
        foreach (DB::table('employees')->whereNotNull('work_emirate')->select('id', 'work_emirate')->get() as $row) {
            $canonical = self::MAP[strtolower(trim($row->work_emirate))] ?? null;
            if ($canonical && $canonical !== $row->work_emirate) {
                DB::table('employees')->where('id', $row->id)->update(['work_emirate' => $canonical]);
            }
        }

        foreach (DB::table('motorbikes')->whereNotNull('emirate')->select('id', 'emirate')->get() as $row) {
            $canonical = self::MAP[strtolower(trim($row->emirate))] ?? null;
            if ($canonical && $canonical !== $row->emirate) {
                DB::table('motorbikes')->where('id', $row->id)->update(['emirate' => $canonical]);
            }
        }
    }

    public function down(): void
    {
        // normalization is not reversible
    }
};
