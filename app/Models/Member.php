<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    /** @use HasFactory<\Database\Factories\MemberFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'cinema_name',
        'address',
        'city',
        'phone',
        'email',
        'website',
        'sort_order',
    ];
}
