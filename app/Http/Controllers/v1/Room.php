<?php

namespace App\Http\Controllers\v1;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use DB;

class Room extends BaseController
{
   /**
     * メンバー作成.
     *
     * @param Request $request
     * @return void
     */
    public function getAll(Request $request) {
        try{
            $ret = [];
            try {
                $ret['code'] = 0;
                $rooms = DB::table('room')->get();
                $players = DB::table('player')->get();
                $ret['rooms'] = [];

                $lstPlayers = [];
                foreach($players as $player){
                    $player->pass = '****';
                    $lstPlayers[$player->roomid][] = $player;
                }
                $lstRooms = [];
                foreach($rooms as $room){
                    $lstRooms [$room->id]['room'] = $room;
                    $lstRooms [$room->id]['players'] = $lstPlayers[$room->id];
                }
                $ret['rooms'] = $lstRooms;
            } catch (\Exception $e) {
                $ret['code'] = 9;
                $ret['error'] = $e;
            }
        }catch(Exception $ex){
        }
        return response()->json($ret);
    }

    /**
     * メンバー作成.
     *
     * @param Request $request
     * @return void
     */
    public function create(Request $request) {
        try{
            $ret = [];
            $params = $request->all();

            DB::beginTransaction();
            try {

                $parameter = $params['parameter'];
                $roomId = DB::table('room')->insertGetId(['name' => $parameter['roomName'], 'period' => $parameter['periodTurn'],]);

                $playerNum = intVal($parameter['playerNum']);
                for($i=0; $i<$playerNum; $i++){
                    DB::table('player')->insert([
                        'roomid' => $roomId,
                        'turn' => 1,
                    ]);
                }

                DB::commit();

                $ret['code'] = 0;
                $ret['roomName'] = $parameter['roomName'];

            } catch (\Exception $e) {
                $ret['code'] = 9;
                $ret['error'] = $e->getMessage();
                DB::rollback();
            }
        }catch(Exception $ex){
        }
        return response()->json($ret);
    }
}
