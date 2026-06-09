<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    // Exclude these fields from audit logs (sensitive / noise)
    protected array $auditExclude = ['updated_at', 'created_at', 'password', 'remember_token'];

    public static function bootAuditable(): void
    {
        static::created(fn($model) => $model->recordAudit('created', [], $model->getAuditValues()));
        static::updated(fn($model) => $model->recordAudit('updated', $model->getOldAuditValues(), $model->getChangedAuditValues()));
        static::deleted(fn($model) => $model->recordAudit('deleted', $model->getAuditValues(), []));
    }

    private function recordAudit(string $action, array $old, array $new): void
    {
        if (empty($old) && empty($new) && $action === 'updated') {
            return;
        }

        $user = Auth::user();

        AuditLog::create([
            'user_id'    => $user?->id,
            'user_name'  => $user?->name,
            'action'     => $action,
            'model_type' => class_basename($this),
            'model_id'   => $this->getKey(),
            'model_ref'  => $this->getAuditRef(),
            'old_values' => empty($old) ? null : $old,
            'new_values' => empty($new) ? null : $new,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }

    private function getAuditValues(): array
    {
        return array_diff_key(
            $this->getAttributes(),
            array_flip($this->auditExclude)
        );
    }

    private function getOldAuditValues(): array
    {
        $changed = array_keys($this->getDirty());
        $old     = [];
        foreach ($changed as $key) {
            if (!in_array($key, $this->auditExclude)) {
                $old[$key] = $this->getOriginal($key);
            }
        }
        return $old;
    }

    private function getChangedAuditValues(): array
    {
        $changed = [];
        foreach ($this->getDirty() as $key => $value) {
            if (!in_array($key, $this->auditExclude)) {
                $changed[$key] = $value;
            }
        }
        return $changed;
    }

    // Override in models to set a human-readable ref
    protected function getAuditRef(): ?string
    {
        foreach (['employee_id', 'bike_id', 'loan_id', 'payroll_id', 'fine_id', 'expense_id', 'income_id', 'maintenance_id'] as $field) {
            if (isset($this->attributes[$field])) {
                return $this->attributes[$field];
            }
        }
        return (string) $this->getKey();
    }
}
