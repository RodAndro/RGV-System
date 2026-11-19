<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait OptimisticLocking
{
    public function lockingUpdate(array $data): bool
    {
        $currentVersion = $this->lock_version;
        $data['lock_version'] = $currentVersion + 1;

        $updated = static::where('id', $this->id)
            ->where('lock_version', $currentVersion)
            ->update($data);

        if ($updated) {
            $this->refresh();
        }

        return (bool) $updated;
    }
}
