<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class Rpa extends Model {
    use HasUuids;

    protected $table = 'rpa';
    protected $fillable = ['name','amount'];
    protected $casts = [
        'amount' => 'float'
    ];

}

