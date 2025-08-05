<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Group extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'name',
        'description',
        'company_id',
        'created_by',
        'logo',
        'is_active',
        'deactivated_at',
        'deactivated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'deactivated_at' => 'datetime',
    ];

    public function members()
    {
        return $this->belongsToMany(User::class, 'group_member')
            ->withPivot(['role', 'added_by'])
            ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deactivatedBy()
    {
        return $this->belongsTo(User::class, 'deactivated_by');
    }

    public function accessControls()
    {
        return $this->hasMany(AccessControl::class);
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function activate()
    {
        return $this->update([
            'is_active' => true,
            'deactivated_at' => null,
            'deactivated_by' => null,
        ]);
    }

    public function deactivate(User $user)
    {
        return $this->update([
            'is_active' => false,
            'deactivated_at' => now(),
            'deactivated_by' => $user->id,
        ]);
    }
}
