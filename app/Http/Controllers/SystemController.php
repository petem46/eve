<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\System;
use Illuminate\Http\Request;

class SystemController extends Controller
{
  public function getSystems()
  {
    $data = [
      'systems' => System::get(),
    ];
    return $data;
  }

  public function emptySystemsTable()
  {
    DB::table('systems')->truncate();
  }

  public function saveSystemData(Request $request)
  {
    $data = $request->json()->all();
    foreach ($data as $alliance) {
      if (isset($alliance['id'])) {
        $id = $alliance['id'];
        $name = $alliance['name'];
        $category = $alliance['name'];
        $id = isset($id) ? $id : '2000';
        $name = isset($name) ? $name : 'Err 2000 ERROR';
        $category = isset($category) ? $category : 'Check API Call Loop at 2000 ERROR';
        $new = System::updateOrCreate([
          'id' => $id,
          'name' => $name,
          'category' => $category,
        ]);
      } else {
        continue;
      }
    }
  }
}
