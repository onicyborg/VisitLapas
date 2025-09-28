<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitQueue extends Model
{
    use HasFactory;

    protected $table = 'visit_queue';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'visit_date', 'ticket_number', 'visitor_id', 'inmate_id',
        'status', 'priority', 'counter_id', 'created_by',
        'called_at', 'started_at', 'ended_at', 'cancelled_reason', 'notes',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'priority' => 'integer',
        'called_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function visitor()
    {
        return $this->belongsTo(Visitors::class, 'visitor_id');
    }

    public function inmate()
    {
        return $this->belongsTo(Inmates::class, 'inmate_id');
    }

    public function counter()
    {
        return $this->belongsTo(Counters::class, 'counter_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function callLogs()
    {
        return $this->hasMany(QueueCallLogs::class, 'queue_id');
    }
}
