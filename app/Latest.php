<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Latest extends Model
{
  protected $fillable = [
    'alliance_id',
    'solar_system_id',
    'structure_id',
    'structure_type_id',
    'vulnerability_occupancy_level',
    'vulnerable_end_time',
    'vulnerable_start_time',
  ];

  protected $table = 'latest';
}
