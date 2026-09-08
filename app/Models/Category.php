<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'code', 'description', 'tolerance_percentage'];

    protected function casts(): array
    {
        return ['tolerance_percentage' => 'decimal:2'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
