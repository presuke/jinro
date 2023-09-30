<?php

namespace App\Http\Controllers\v1;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use DB;

class Player extends BaseController
{
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
            $ret['request'] = $params;
            DB::beginTransaction();
            try {

                $work = DB::table('work')->where(
                    [
                        'id' => $params['workid']
                    ]
                )->first();

                $player = DB::table('player')->where(
                    [
                        'roomid' => $params['roomid'],
                        'id' => $params['id']
                    ]
                );
                $player->update(
                    [
                        'name' => $params['name'],
                        'sex' => $params['sex'],
                        'img' => $params['img'],
                        'pass' => $params['pass'],
                        'workid' => $params['workid'],
                        'lifelevel' => $work->lifelevelMin,
                        'money' => $work->salary,
                    ]
                );
                DB::commit();
                $ret['player'] = $player->first();
                $ret['msg'] = 'プレイヤーを作成しました！';
            } catch (\Exception $e) {
                $ret['code'] = 9;
                $ret['error'] = $e;
                DB::rollback();
            }
        } catch (Exception $ex) {
        }
        return response()->json($ret);
    }
}
