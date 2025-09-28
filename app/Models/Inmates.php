<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inmates extends Model
{
    use HasFactory;

    protected $table = 'inmates';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'register_no', 'name', 'gender', 'birth_date', 'cell_block', 'status', 'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function visitQueues()
    {
        return $this->hasMany(VisitQueue::class, 'inmate_id');
    }
}
