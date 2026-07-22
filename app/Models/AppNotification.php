<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AppNotification extends Model
{
    protected $fillable = ['notifiable_type', 'notifiable_id', 'type', 'title', 'body', 'link', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function notify(Model $notifiable, string $type, string $title, ?string $body = null, ?string $link = null): void
    {
        static::create([
            'notifiable_type' => $notifiable->getMorphClass(),
            'notifiable_id' => $notifiable->getKey(),
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'link' => $link,
        ]);
    }
}
