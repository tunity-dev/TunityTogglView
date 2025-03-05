<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = ['contract_type'];

    const FULLTIME = 'Fulltime';
    const PARTTIME = 'Parttime';
}
