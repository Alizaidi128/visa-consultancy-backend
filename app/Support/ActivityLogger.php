<?php

namespace App\Support;

use App\Models\ActivityLog;

class ActivityLogger
{
    public static function log(
        string $action,
               $model,
        array $data = []
    ): void {
        ActivityLog::create([
            'action' => $action,
            'loggable_type' => get_class($model),
            'loggable_id' => $model->id,
            'user_id' => auth()->id(),
            'data' => $data,
        ]);
    }
}
