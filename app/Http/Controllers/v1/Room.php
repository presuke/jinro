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
    public function getAll(Request $request)
    {
        try {
            $ret = [];
            try {
                $ret['code'] = 0;
                $rooms = DB::table('room')->get();
                $ret['rooms'] = [];

                $lstRooms = [];
                foreach ($rooms as $room) {
                    $lstRooms[$room->id]['room'] = $room;
                    $lstRooms[$room->id]['players'] = [];
                }

                $players = DB::table('player')->select('id', 'roomid', 'name', 'sex', 'img')->get();
                foreach ($players as $player) {
                    $lstRooms[$player->roomid]['players'][] = $player;
                }

                $ret['rooms'] = $lstRooms;
            } catch (\Exception $e) {
                $ret['code'] = 9;
                $ret['error'] = $e;
            }
        } catch (Exception $ex) {
        }
        return response()->json($ret);
    }

    /**
     * メンバー作成.
     *
     * @param Request $request
     * @return void
     */
    public function create(Request $request)
    {
        try {
            $ret = [];
            $params = $request->all();

            $ret['step'] = 1;
            DB::beginTransaction();
            try {

                $params = $params['params'];

                $existRoom = DB::table('room')->where(['name' => $params['name']])->count();
                if ($existRoom > 0) {
                    $ret['code'] = 9;
                    $ret['error'] = '部屋名"' . $params['name'] . '"は既に存在します。別の部屋名を指定してください。';
                    return $ret;
                }

                //部屋作成
                $ret['step'] = 2;
                $roles = [];
                foreach ($params['roles'] as $roleId => $role) {
                    $roles[$roleId]['id'] = $role['id'];
                    $roles[$roleId]['name'] = $role['name'];
                    $roles[$roleId]['num'] = $role['num'];
                }
                $roomId = DB::table('room')->insertGetId(['name' => $params['name'], 'roles' => json_encode($roles, JSON_UNESCAPED_UNICODE),]);

                //プレイヤー作成
                $ret['step'] = 3;
                foreach ($roles as $roleId => $role) {
                    for ($i = 0; $i < $role['num']; $i++) {
                        DB::table('player')->insert([
                            'roomid' => $roomId,
                        ]);
                    }
                }

                $ret['step'] = 4;
                $ret = $this->shuffleCard($roomId);
                $ret['step'] = 5;
                if ($ret['code'] == 0) {
                    DB::commit();
                    $ret['code'] = 0;
                    $ret['roomName'] = $params['name'];
                } else {
                    DB::rollback();
                    $ret['code'] = 0;
                    $ret['roomName'] = $params['name'];
                    $ret['error'] = 'debug';
                }
            } catch (\Exception $e) {
                $ret['code'] = 9;
                $ret['error'] = $e->getMessage();
                DB::rollback();
            }
        } catch (\Exception $e) {
            $ret['code'] = 99;
            $ret['error'] = $e->getMessage();
        }
        return response()->json($ret);
    }

    public function remove(Request $request)
    {
        try {
            $ret = [];
            $params = $request->all();

            DB::beginTransaction();
            try {

                $params = $params['params'];

                $roomId = $params['id'];

                $histories = DB::table('history')->where(['roomid' => $roomId]);
                $histories->delete();

                $players = DB::table('player')->where(['roomid' => $roomId]);
                $players->delete();

                $room = DB::table('room')->where(['id' => $roomId])->first();
                $room->delete();

                $ret['code'] = 0;
            } catch (\Exception $e) {
                $ret['code'] = 9;
                $ret['error'] = $e->getMessage();
                DB::rollback();
            }
        } catch (\Exception $ex) {
            $ret['code'] = 9;
            $ret['error'] = $e->getMessage();
        }
        return response()->json($ret);
    }

    public function restart(Request $request)
    {
        try {
            $ret = [];
            $params = $request->all();

            DB::beginTransaction();
            try {

                $params = $params['params'];

                $roomId = $params['id'];

                $histories = DB::table('history')->where(['roomid' => $roomId]);
                $histories->delete();

                DB::table('room')->where(['id' => $roomId])->update(['day' => 1, 'time' => 0, 'win' => 0]);

                $ret = $this->shuffleCard($roomId);
                if ($ret['code'] == 0) {
                    DB::commit();
                    $ret['code'] = 0;
                } else {
                    DB::rollback();
                    $ret['code'] = 9;
                    $ret['error'] = 'debug';
                }
            } catch (\Exception $e) {
                $ret['code'] = 9;
                $ret['error'] = $e->getMessage();
                DB::rollback();
            }
        } catch (\Exception $ex) {
        }
        return response()->json($ret);
    }

    public function shuffleCard($roomId)
    {
        $ret = [];
        try {
            $room = DB::table('room')->select('roles')->where([
                'id' => $roomId,
            ])->first();
            $roles = json_decode($room->roles);
            $cards = [];
            foreach ($roles as $roleid => $role) {
                for ($i = 0; $i < $role->num; $i++) {
                    $cards[] = $roleid;
                }
            }
            shuffle($cards);

            $players = DB::table('player')->where([
                'roomid' => $roomId,
            ])->get();

            $cardNo = 0;
            foreach ($players as $player) {
                DB::table('player')->where([
                    'id' => $player->id,
                    'roomid' => $roomId,
                ])->update(['flgDead' => 0, 'role' => $cards[$cardNo]]);
                $cardNo++;
            }

            $ret['code'] = 0;
        } catch (\Exception $e) {
            $ret['code'] = 8;
            $ret['error'] = $e->getMessage();
        }
        return $ret;
    }
}
