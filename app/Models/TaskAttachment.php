<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskAttachment extends Model
{
    protected $fillable = [
        'task_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
