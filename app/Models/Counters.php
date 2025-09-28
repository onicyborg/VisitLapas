<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Counters extends Model
{
    use HasFactory;

    protected $table = 'counters';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'code', 'name', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function visitQueues()
    {
        return $this->hasMany(VisitQueue::class, 'counter_id');
    }

    public function callLogs()
    {
        return $this->hasMany(QueueCallLogs::class, 'counter_id');
    }
}
