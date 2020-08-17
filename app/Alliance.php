<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alliance extends Model
{
    protected $fillable = [
      'id',
      'name',
      'ticker'
    ];

}
