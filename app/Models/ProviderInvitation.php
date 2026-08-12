<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderInvitation extends Model
{
    protected $fillable = ['email', 'invited_by_admin_id', 'source', 'sent_at', 'registered_at'];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'registered_at' => 'datetime',
        ];
    }

    /** Created but not yet mailed; the scheduled command works through these. */
    public function scopeQueued($query)
    {
        return $query->whereNull('sent_at');
    }

    /**
     * Whether this address already has a provider account. Checked live rather
     * than stored, so an invitee who signs up later shows as registered
     * without anything having to write back to this row.
     */
    public function hasRegistered(): bool
    {
        return $this->registered_at !== null
            || Inspector::where('email', $this->email)->exists();
    }
}
