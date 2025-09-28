<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitors extends Model
{
    use HasFactory;

    protected $table = 'visitors';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'national_id', 'name', 'gender', 'birth_date', 'phone', 'address',
        'relation_note', 'photo_url', 'is_blacklisted', 'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'is_blacklisted' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function visitQueues()
    {
        return $this->hasMany(VisitQueue::class, 'visitor_id');
    }
}
