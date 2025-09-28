<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisplaySettings extends Model
{
    use HasFactory;

    protected $table = 'display_settings';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    // only updated_at exists, no created_at
    public $timestamps = false;

    protected $fillable = [
        'id', 'theme', 'voice_enabled', 'ticker_text', 'updated_by', 'updated_at',
    ];

    protected $casts = [
        'voice_enabled' => 'boolean',
        'updated_at' => 'datetime',
    ];

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
