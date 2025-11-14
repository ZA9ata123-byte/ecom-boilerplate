<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Metafield extends Model
{
    use HasFactory;

    protected $fillable = [
        'metafieldable_id',
        'metafieldable_type',
        'namespace',
        'key',
        'value',
        'type',
    ];

    public function metafieldable(): MorphTo
    {
        return $this->morphTo();
    }
}
