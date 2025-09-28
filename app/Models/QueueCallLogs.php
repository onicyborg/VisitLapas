<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueueCallLogs extends Model
{
    use HasFactory;

    protected $table = 'queue_call_logs';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    // only created_at exists in the table, managed by DB default
    public $timestamps = false;

    protected $fillable = [
        'id', 'queue_id', 'called_by', 'counter_id', 'call_no', 'message', 'created_at',
    ];

    protected $casts = [
        'call_no' => 'integer',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function queue()
    {
        return $this->belongsTo(VisitQueue::class, 'queue_id');
    }

    public function caller()
    {
        return $this->belongsTo(User::class, 'called_by');
    }

    public function counter()
    {
        return $this->belongsTo(Counters::class, 'counter_id');
    }
}
