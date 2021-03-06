<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
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

  public function emptyAlliancesTable()
  {
    DB::table('alliances')->truncate();
  }

  public function saveAllianceData(Request $request)
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
        $new = Alliance::updateOrCreate([
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
