<?php

namespace App\Http\Controllers;

use App\Alliance;
use Illuminate\Http\Request;

class AllianceController extends Controller
{
  public function getAlliances()
  {
    $data = [
      'alliances' => Alliance::get(),
    ];
    return $data;
  }
}
