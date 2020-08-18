<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Latest;
use App\Alliance;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AppController extends Controller
{
  public function index()
  {
    return view('/home');
  }

  public function emptyLatest()
  {
    DB::table('latest')->truncate();
  }

  public function saveLatest(Request $request)
  {
    DB::table('latest')->truncate();
    for ($i = 0; $i < count($request['data']); $i++) {
      $item = $request['data'][$i];
      $alliance_id = isset($item['alliance_id']) ? $item['alliance_id'] : '';
      $solar_system_id = isset($item['solar_system_id']) ? $item['solar_system_id'] : '';
      $structure_id = isset($item['structure_id']) ? $item['structure_id'] : '';
      $structure_type_id = isset($item['structure_type_id']) ? $item['structure_type_id'] : '';
      $vulnerability_occupancy_level = isset($item['vulnerability_occupancy_level']) ? $item['vulnerability_occupancy_level'] : '';
      $vulnerable_end_time = isset($item['vulnerable_end_time']) ? $item['vulnerable_end_time'] : '';
      $vulnerable_start_time = isset($item['vulnerable_start_time']) ? $item['vulnerable_start_time'] : '';

      $new = Latest::create([
        'alliance_id' => $alliance_id,
        'solar_system_id' => $solar_system_id,
        'structure_id' => $structure_id,
        'structure_type_id' => $structure_type_id,
        'vulnerability_occupancy_level' => $vulnerability_occupancy_level,
        'vulnerable_end_time' => $vulnerable_end_time,
        'vulnerable_start_time' => $vulnerable_start_time,
      ]);
    }
    return response('Latest Updated!!!', Response::HTTP_OK);
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
        $ticker = $alliance['name'];
        $id = isset($id) ? $id : '2000';
        $name = isset($name) ? $name : 'Err 2000 ERROR';
        $ticker = isset($ticker) ? $ticker : 'Check API Call Loop at 2000 ERROR';
        $new = Alliance::updateOrCreate([
          'id' => $id,
          'name' => $name,
          'ticker' => $ticker,
        ]);
      } else {
        continue;
      }
    }
  }
}
