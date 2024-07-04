<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class MarketPlace extends Model
{
    use HasFactory;

    protected $fillable = [];

    public $extra = [];

    protected $casts = [];
    public static $rules = [
        "name" => '',
"url" => '',
    ];
}
