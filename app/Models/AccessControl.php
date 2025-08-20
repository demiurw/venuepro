<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class AccessControl extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'access_control';
    
    const UPDATED_AT = null; // Disable updated_at
    
    protected $dates = ['created_at'];

    protected $fillable = [
        'group_id',
        'entity_id',
        'entity_type', // e.g., 'room', 'building'
        'access_level', // 'view', 'book', 'manage'
        'company_id',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'entity_id')->where('entity_type', 'room');
    }

    public function building()
    {
        return $this->belongsTo(Building::class, 'entity_id')->where('entity_type', 'building');
    }
}
