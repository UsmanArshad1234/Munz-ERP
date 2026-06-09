<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;

class SettingService
{
    public function getAllGrouped(): array
    {
        return Setting::active()
            ->orderBy('type')
            ->orderBy('sort_order')
            ->get(['id', 'type', 'value', 'label', 'sort_order', 'is_default'])
            ->groupBy('type')
            ->toArray();
    }

    public function getByType(string $type): Collection
    {
        return Setting::active()
            ->ofType($type)
            ->orderBy('sort_order')
            ->get(['id', 'value', 'label', 'sort_order', 'is_default']);
    }

    public function create(array $data): Setting
    {
        return Setting::create([
            'type'       => $data['type'],
            'value'      => \Str::slug($data['label'], '_'),
            'label'      => $data['label'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active'  => true,
            'is_default' => false,
        ]);
    }

    public function update(Setting $setting, array $data): Setting
    {
        $setting->update(array_filter([
            'label'      => $data['label'] ?? null,
            'sort_order' => $data['sort_order'] ?? null,
            'is_active'  => $data['is_active'] ?? null,
        ], fn($v) => $v !== null));

        return $setting->fresh();
    }

    public function delete(Setting $setting): void
    {
        if ($setting->is_default) {
            throw new \Exception('Default system settings cannot be deleted.');
        }

        $setting->delete();
    }

    public function reorder(string $type, array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            Setting::where('id', $id)->where('type', $type)
                   ->update(['sort_order' => $index]);
        }
    }

    public function getTypes(): array
    {
        return Setting::TYPES;
    }
}
