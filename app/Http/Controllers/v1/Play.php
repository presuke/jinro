<?php

namespace App\Http\Controllers\v1;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Service\Auth;
use App\Service\Action;
use DB;

class Play extends BaseController
{

    public function action(Request $request)
    {
        try {
            $ret = [];
            $ret['code'] = 0;
            $params = $request->all();
            $params = $params['params'];
            try {
                $playerid = $params['playerid'];
                $authToken = $params['authtoken'];

                $checkStatus = Auth::checkAcuthToken($playerid, $authToken);
                if ($checkStatus == 0) {
                    //自プレイヤー
                    $me = DB::table('player')->select('id', 'roomid', 'turn', 'workid', 'money', 'stress', 'lifelevel', 'flgFire')->where(
                        [
                            'id' => $playerid,
                        ]
                    )->first();

                    $action = $params['actionMode'];
                    switch ($action) {
                        case 'drowCard': {
                                //fire判定前後で変化がないかチェック
                                $isFireBefore = $me->flgFire;
                                Action::jadgeFire($me);

                                if ($isFireBefore == 0 && $me->flgFire == 0) {
                                    //ラットレース継続
                                    Action::drowCard($me);
                                } else if ($isFireBefore == 1 && $me->flgFire == 1) {
                                    //ファイア継続
                                    Action::drowCardFire($me);
                                } else if ($isFireBefore == 2 && $me->flgFire == 2) {
                                    //ファイア継続
                                    Action::drowCardFire($me);
                                } else if ($isFireBefore == 1 && $me->flgFire == 2) {
                                    //勝ち
                                    Action::changeFire($me, 2);
                                } else if ($isFireBefore == 0 && $me->flgFire == 1) {
                                    //ファイア昇格
                                    Action::changeFire($me, 1);
                                } else {
                                    //ラットレース降格
                                    Action::changeFire($me, 0);
                                }
                                break;
                            }

                        case 'work': {
                                Action::work($me);
                                break;
                            }

                        case 'treat': {
                                Action::treat($me);
                                break;
                            }

                        case 'riseLifeLevel': {
                                Action::changeLifeLevel($me, true);
                                break;
                            }

                        case 'dropLifeLevel': {
                                Action::changeLifeLevel($me, false);
                                break;
                            }

                        case 'buyEstate': {
                                $ret = Action::buyEstate($me);
                                break;
                            }

                        case 'buyStock': {
                                $ret = Action::buyStock($me, $params);
                                break;
                            }

                        case 'trade': {
                                $ret = Action::trade($me, $params);
                                break;
                            }

                        case 'lostStock':
                        case 'lostEstate':
                        case 'noTrade': {
                                $ret = Action::lostChance($me, $action);
                                break;
                            }

                        case 'banking': {
                                $ret = Action::banking($me, $params['amount']);
                                break;
                            }

                        case 'confirm': {
                                $ret = Action::confirm($me, $params['crntPlayer']);
                                break;
                            }

                        case 'period': {
                                Action::period($me);
                                break;
                            }

                        default: {
                                $ret['error'] = 'undefined action[' . $action . ']';
                                break;
                            }
                    }
                } else {
                    $ret['code'] = $checkStatus;
                }
            } catch (\Exception $e) {
                $ret['code'] = 99;
                $ret['errors'][] = $e;
            }
        } catch (\Exception $ex) {
            $ret['code'] = 99;
            $ret['errors'][] = $ex;
        }
        return response()->json($ret);
    }

    /**
     * 現状の部屋の状態.
     *
     * @param Request $request
     * @return void
     */
    public function getRoomStatus(Request $request)
    {
        try {
            $ret = [];
            $ret['code'] = 0;
            $params = $request->all();
            try {
                $playerid = $params['playerid'];

                if (isset($params['authtoken'])) {
                    $authToken = $params['authtoken'];
                } else {
                    $authToken = '';
                }

                $checkStatus = Auth::checkAcuthToken($playerid, $authToken);
                if ($checkStatus == 0) {

                    //自プレイヤー
                    $me = DB::table('player')->select('id', 'roomid', 'name', 'turn', 'workid', 'lifelevel')->where(
                        [
                            'id' => $playerid,
                        ]
                    )->first();

                    //自分名無しエラー
                    if ($me->name == '' || $me->name == null) {
                        $ret['code'] = 8;
                    } else {

                        //自ルーム
                        $room = DB::table('room')->where(
                            [
                                'id' => $me->roomid,
                            ]
                        )->first();

                        //自ルームのプレイヤー
                        $players = DB::table('player')->select('id', 'roomid', 'name', 'sex', 'img', 'money', 'workid', 'stress', 'turn', 'lifelevel', 'flgFire')->where(
                            [
                                'roomid' => $me->roomid,
                            ]
                        )->orderBy('id')->get();

                        //他プレイヤー名無しエラー
                        foreach ($players as $player) {
                            if ($player->name == '') {
                                $ret['code'] = 7;
                            }
                        }
                        if ($ret['code'] == 0) {

                            //職業
                            $works = [];
                            $lstWork = DB::table('work')->get();
                            foreach ($lstWork as $work) {
                                $works[$work->id] = $work;
                            }

                            //資産
                            $assets = DB::table('asset')->where(
                                [
                                    'roomid' => $me->roomid,
                                ]
                            )->orderBy('playerid')->get();

                            //順番の人を特定
                            $crntPlayer = $players[0];
                            for ($idx = 0; $idx < count($players); $idx++) {
                                $player = $players[$idx];
                                if ($player->turn < $crntPlayer->turn) {
                                    $crntPlayer = $player;
                                    break;
                                }
                            }

                            //直近のプレイヤーのアクション(confirm以外)
                            $action = [];
                            $history = DB::table('history')->select('roomid', 'playerid', 'turn', 'action', 'parameter')->where(
                                [
                                    'roomid' => $me->roomid,
                                ]
                            )->where('action', '<>', 'confirm')->orderBy('ins', 'DESC')->first();
                            if ($history == null) {
                                $action['event'] = 0;
                            } else {
                                $action['turn'] = $history->turn;
                                $action['action'] = $history->action;
                                $parameter = json_decode($history->parameter);
                                $action['parameter'] = $parameter;
                                switch ($history->action) {
                                    case 'drowCard': {
                                            //資産再取得
                                            $assets = DB::table('asset')->select(
                                                'id',
                                                'roomid',
                                                'playerid',
                                                'turn',
                                                'type',
                                                'buy',
                                                'sell',
                                                'return',
                                                'has',
                                                DB::raw("'false' as trade")
                                            )->where(
                                                [
                                                    'roomid' => $me->roomid,
                                                ]
                                            )->orderBy('playerid')->get();
                                            $action['event'] = $parameter->event;
                                            break;
                                        }

                                    case 'loan': {
                                            $action['event'] = $parameter->event;
                                            break;
                                        }

                                    case 'work':
                                    case 'treat':
                                    case 'riseLifeLevel':
                                    case 'dropLifeLevel':
                                    case 'trade':
                                    case 'noTrade':
                                    case 'buyStock':
                                    case 'lostStock':
                                    case 'buyEstate':
                                    case 'lostEstate':
                                    case 'sic':
                                    case 'periodComplete':
                                    case 'riseZone':
                                    case 'dropZone':
                                    case 'win':
                                    case 'confirm': {
                                            $action['event'] = 99;
                                            break;
                                        }

                                    case 'confirmAll': {
                                            $action['event'] = 0;
                                            break;
                                        }

                                    default: {
                                            $action['event'] = 9;
                                            break;
                                        }
                                }
                            }

                            $ret['me'] = $me;
                            $ret['room'] = $room;
                            $ret['works'] = $works;
                            $ret['players'] = $players;
                            $ret['assets'] = $assets;
                            $ret['crntPlayer'] = $crntPlayer;
                            $ret['action'] = $action;
                        }
                    }
                } else {
                    $ret['code'] = $checkStatus;
                }
            } catch (\Exception $ex) {
                $ret['code'] = 99;
                $ret['errors'][] = $ex;
            }
        } catch (\Exception $ex) {
            $ret['code'] = 99;
            $ret['errors'][] = $ex;
        }
        return response()->json($ret);
    }


    /**
     * ログイン.
     *
     * @param Request $request
     * @return void
     */
    public function login(Request $request)
    {
        try {
            $ret = [];
            $params = $request->all();
            try {
                $playerid = $params['playerid'];
                $player = DB::table('player')->where(
                    [
                        'id' => $playerid,
                    ]
                )->first();

                if ($player == null) {
                    $ret['code'] = 1;
                    $ret['error'] = '指定されたユーザは存在しません。';
                } else if ($player->pass == $params['pass']) {
                    $ret['code'] = 0;
                    $ret['token'] = Auth::getAuthToken($playerid);
                } else {
                    $ret['code'] = 1;
                    $ret['error'] = 'パスワードが違います。';
                }
            } catch (\Exception $ex) {
                $ret['code'] = 9;
                $ret['error'] = $ex->getMessage();
            }
        } catch (\Exception $ex) {
            $ret['code'] = 9;
            $ret['error'] = $ex->getMessage();
        }
        return response()->json($ret);
    }
}
