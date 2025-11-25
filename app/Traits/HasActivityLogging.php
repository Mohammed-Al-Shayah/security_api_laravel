<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait HasActivityLogging
{
    /**
     * Register the model events for logging.
     */
    protected static function bootHasActivityLogging(): void
    {
        static::created(function (Model $model) {
            self::writeActivityLog('CREATED', $model);
        });

        static::updated(function (Model $model) {
            self::writeActivityLog('UPDATED', $model);
        });

        static::deleted(function (Model $model) {
            self::writeActivityLog('DELETED', $model);
        });
    }

    /**
     * Write a log entry into activity_logs table.
     */
    protected static function writeActivityLog(string $event, Model $model): void
    {
        // اسم الموديل: INCIDENT, PROJECT, SHIFT, ...
        $modelName = strtoupper(class_basename($model));

        ActivityLog::create([
            'user_id'    => Auth::id(),
            'action'     => $event . '_' . $modelName, // مثال: CREATED_INCIDENT
            'model_type' => get_class($model),
            'model_id'   => $model->getKey(),
            'ip'         => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
