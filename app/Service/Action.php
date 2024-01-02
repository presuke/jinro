<?php

namespace App\Service;

use DB;

class Action
{
  public static function vote($me, $room, $targetid)
  {
    $ret = [];
    try {

      DB::beginTransaction();
      //history
      $myAction = DB::table('history')->where(
        [
          'roomid' => $room->id,
          'playerid' => $me->id,
          'day' => $room->day,
          'time' => $room->time,
          'action' => 'vote',
        ]
      );
      if ($myAction->count() == 0) {
        DB::table('history')->insert([
          'roomid' => $room->id,
          'playerid' => $me->id,
          'day' => $room->day,
          'time' => $room->time,
          'action' => 'vote',
          'targetid' => $targetid,
        ]);
      } else {
        $myAction->update(['targetid' => $targetid]);
      }
      DB::commit();
      $ret['message'] = '投票完了しました。';
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function confirm($me, $room, $param)
  {
    $ret = [];
    try {

      DB::beginTransaction();
      //history
      $action = DB::table('history')->where(
        [
          'roomid' => $room->id,
          'playerid' => $me->id,
          'day' => $room->day,
          'time' => $room->time,
          'action' => $param['action'],
        ]
      );
      if ($action->count() == 0) {
        DB::table('history')->insert(
          [
            'roomid' => $room->id,
            'playerid' => $me->id,
            'targetid' => 0,
            'day' => $room->day,
            'time' => $room->time,
            'action' => $param['action'],
          ],
        );
      }
      DB::commit();
      $ret['message'] = $param['message'] . '完了しました。';
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function sleep($me, $room)
  {
    $ret = [];
    try {
      $actionName = 'sleep';
      $myAction = [
        'roomid' => $room->id,
        'playerid' => $me->id,
        'targetid' => 0,
        'day' => $room->day,
        'time' => $room->time,
      ];

      //history
      $action = DB::table('history')->where($myAction);
      if ($action->count() == 0) {
        $myAction['action'] = $actionName;
        DB::beginTransaction();
        DB::table('history')->insert($myAction);
        DB::commit();
      }
      $ret['message'] = '就寝しました。';
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function attack($me, $room, $targetid)
  {
    $ret = [];
    try {
      $actionName = 'attack';
      if ($me->role != 1) {
        $ret['error'] = 'あなたは人狼ではありません。';
        return $ret;
      }

      $myAction = [
        'roomid' => $room->id,
        'playerid' => $me->id,
        'day' => $room->day,
        'time' => $room->time,
      ];
      $action = DB::table('history')->where($myAction);
      DB::beginTransaction();
      if ($action->count() == 0) {
        $myAction['action'] = $actionName;
        $myAction['targetid'] = $targetid;
        DB::table('history')->insert($myAction);
      } else {
        $action->update(['action' => $actionName, 'targetid' => $targetid]);
      }
      DB::commit();
      $ret['message'] = '襲撃完了しました。';
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function save($me, $room, $targetid)
  {
    $ret = [];
    try {
      $actionName = 'save';
      if ($me->role != 2) {
        $ret['error'] = 'あなたは狩人ではありません。';
        return $ret;
      }
      if ($room->day > 1) {
        $lastTarget = DB::table('history')->where(
          [
            'roomid' => $room->id,
            'playerid' => $me->id,
            'day' => $room->day - 1,
            'time' => $room->time,
            'action' => $actionName,
          ]
        )->first();
        if ($lastTarget->targetid == $targetid) {
          $ret['error'] = '昨日と同じ人を守ることはできません。';
          return $ret;
        }
      }
      $myAction = [
        'roomid' => $room->id,
        'playerid' => $me->id,
        'day' => $room->day,
        'time' => $room->time,
      ];
      $action = DB::table('history')->where($myAction);

      DB::beginTransaction();
      if ($action->count() == 0) {
        $myAction['action'] = $actionName;
        $myAction['targetid'] = $targetid;
        DB::table('history')->insert($myAction);
      } else {
        $action->update(['action' => $actionName, 'targetid' => $targetid]);
      }
      DB::commit();
      $ret['message'] = '防衛完了しました。';
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function predict($me, $room, $targetid)
  {
    $ret = [];
    try {
      $actionName = 'predict';
      if ($me->role != 3) {
        $ret['error'] = 'あなたは占い師ではありません。';
        return $ret;
      }
      $target = DB::table('player')->where(
        [
          'roomid' => $room->id,
          'id' => $targetid,
        ]
      )->first();
      if ($target->flgDead == 1) {
        $ret['error'] = '拉致・投獄されたプレイヤーを占うことはできません。';
        return $ret;
      }

      $myAction = [
        'roomid' => $room->id,
        'playerid' => $me->id,
        'day' => $room->day,
        'time' => $room->time,
      ];
      $action = DB::table('history')->where($myAction);

      if ($action->count() == 0) {
        $myAction['action'] = $actionName;
        $myAction['targetid'] = $targetid;
        DB::beginTransaction();
        DB::table('history')->insert($myAction);
        DB::commit();
      } else {
        $ret['error'] = '1日に占うことができるのは1人のみです。';
      }
      $action = DB::table('history')->where($myAction)->first();
      $target = DB::table('player')->where(
        [
          'roomid' => $room->id,
          'id' => $action->targetid,
        ]
      )->first();
      if ($target->role == 5) {
        $target->role = 0;
      }
      $ret['result'][$actionName]['target'] = $target;
      if (!isset($ret['message'])) {
        $ret['message'] = $target->name . 'さんを占いました。';
      }
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function expose($me, $room, $targetid)
  {
    $ret = [];
    try {
      $actionName = 'expose';
      if ($me->role != 4) {
        $ret['error'] = 'あなたは霊媒師ではありません。';
        return $ret;
      }
      $target = DB::table('player')->where(
        [
          'roomid' => $room->id,
          'id' => $targetid,
        ]
      )->first();
      if ($target->flgDead == 0) {
        $ret['error'] = '霊媒対象は拉致・投獄されたプレイヤーだけです。';
        return $ret;
      }

      $myAction = [
        'roomid' => $room->id,
        'playerid' => $me->id,
        'day' => $room->day,
        'time' => $room->time,
      ];
      $action = DB::table('history')->where($myAction);

      if ($action->count() == 0) {
        $myAction['action'] = $actionName;
        $myAction['targetid'] = $targetid;
        DB::beginTransaction();
        DB::table('history')->insert($myAction);
        DB::commit();
      } else {
        $ret['error'] = '1日に霊媒することができるのは1人のみです。';
      }
      $action = DB::table('history')->where($myAction)->first();
      $target = DB::table('player')->where(
        [
          'roomid' => $room->id,
          'id' => $action->targetid,
        ]
      )->first();

      $ret['result'][$actionName]['target'] = $target;
      if (!isset($ret['message'])) {
        $ret['message'] = $target->name . 'さんを霊媒しました。';
      }
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function change($me, $room, $targetid)
  {
    $ret = [];
    try {
      $actionName = 'change';
      if ($me->role != 7) {
        $ret['error'] = 'あなたは吸血鬼ではありません。';
        return $ret;
      }
      $target = DB::table('player')->where(
        [
          'roomid' => $room->id,
          'id' => $targetid,
        ]
      )->first();
      if ($target->flgDead == 1) {
        $ret['error'] = '襲撃対象は拉致・投獄されていないプレイヤーだけです。';
        return $ret;
      }

      $myAction = [
        'roomid' => $room->id,
        'playerid' => $me->id,
        'day' => $room->day,
        'time' => $room->time,
      ];
      $action = DB::table('history')->where($myAction);

      DB::beginTransaction();
      if ($action->count() == 0) {
        $myAction['action'] = $actionName;
        $myAction['targetid'] = $targetid;
        DB::table('history')->insert($myAction);
      } else {
        $action->update(['action' => $actionName, 'targetid' => $targetid]);
      }
      DB::commit();
      $ret['message'] = '襲撃完了しました。';
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function freedom($me, $room, $targetid)
  {
    $ret = [];
    try {
      $actionName = 'freedom';
      if ($me->role != 9) {
        $ret['error'] = 'あなたは天使ではありません。';
        return $ret;
      }
      $target = DB::table('player')->where(
        [
          'roomid' => $room->id,
          'id' => $targetid,
        ]
      )->first();
      if ($target->flgDead == 0) {
        $ret['error'] = '救出対象は拉致・投獄されているプレイヤーだけです。';
        return $ret;
      }

      $myAction = [
        'roomid' => $room->id,
        'playerid' => $me->id,
        'targetid' => $targetid,
        'day' => $room->day,
        'time' => $room->time,
      ];
      $action = DB::table('history')->where($myAction);

      DB::beginTransaction();
      if ($action->count() == 0) {
        $myAction['action'] = $actionName;
        DB::table('history')->insert($myAction);
      } else {
        $action->update(['action' => $actionName, 'targetid' => $targetid]);
      }
      DB::commit();
      $ret['message'] = '救出完了しました。';
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function consider($me, $room, $targetid)
  {
    $ret = [];
    try {
      $myAction = [
        'roomid' => $room->id,
        'playerid' => $me->id,
        'day' => $room->day,
        'time' => $room->time,
      ];
      $action = DB::table('history')->where($myAction);

      $rand = rand(1, 100);
      if ($rand > 50) {
        $actionName = 'sleep';
      } else {
        $actionName = 'consider';
      }
      $rand = rand(1, 100);
      if ($rand > 50) {
        $ret['result']['consider'] = 'voice';
      }

      DB::beginTransaction();
      if ($action->count() == 0) {
        $myAction['action'] = $actionName;
        DB::table('history')->insert($myAction);
      } else {
        $action->update(['action' => $actionName, 'targetid' => $targetid]);
      }
      DB::commit();
      $ret['message'] = '検討しました。';
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }
  /*
  public static function lostChance($me, $action)
  {
    $ret = [];
    try {
      //history
      DB::table('history')->insert([
        'roomid' => $me->roomid,
        'playerid' => $me->id,
        'turn' => $me->turn,
        'action' => $action,
        'parameter' => '',
      ]);
      $ret['message'] = '購入を見送りました。';
    } catch (\Exception $ex) {
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function  changeLifeLevel($me, $isRise)
  {
    try {
      $mywork = DB::table('work')->select('lifelevelMax', 'lifelevelMin')->where(
        [
          'id' => $me->workid,
        ]
      )->first();

      $parameter = [];

      DB::beginTransaction();
      if ($me->flgFire == 0) {
        if ($isRise) {
          $action['action'] = 'riseLifeLevel';
          if ($me->lifelevel < $mywork->lifelevelMax) {
            $player = DB::table('player')->where(
              [
                'roomid' => $me->roomid,
                'id' => $me->id,
              ]
            )->update(
              [
                'lefelevel' => $me->lifelevel + 1,
              ]
            );
            $parameter['result'] = 'success';
            $parameter['lefelevel']['from'] = $me->lifelevel;
            $parameter['lefelevel']['to'] = $me->lifelevel + 1;
          } else {
            $parameter['result'] = 'faild';
            $parameter['lefelevel']['from'] = $me->lifelevel;
            $parameter['lefelevel']['to'] = $me->lifelevel;
          }
        } else {
          $action['action'] = 'dropLifeLevel';
          if ($me->lifelevel > $mywork->lifelevelMin) {
            $lifelevel = $me->lifelevel - 1;
            //ライフレベルが最高に達していたら、運が良ければ2つ下げる。
            if ($me->lifelevel == $mywork->lifelevelMax) {
              $lifelevel = $me->lifelevel - rand(1, 2);
            }
            DB::table('player')->where(
              [
                'roomid' => $me->roomid,
                'id' => $me->id,
              ]
            )->update(
              [
                'lifelevel' => $lifelevel,
              ]
            );
            $parameter['result'] = 'success';
            $parameter['lefelevel']['from'] = $lifelevel;
            $parameter['lefelevel']['to'] = $me->lifelevel;
          } else {
            $parameter['result'] = 'faild';
            $parameter['lefelevel']['from'] = $me->lifelevel;
            $parameter['lefelevel']['to'] = $me->lifelevel;
          }
        }
      } else {
        if ($isRise) {
          if ($me->lifelevel == 10) {
            $parameter['result'] = 'faild';
            $parameter['lefelevel']['from'] = $me->lifelevel;
            $parameter['lefelevel']['to'] = $me->lifelevel;
          } else {
            DB::table('player')->where(
              [
                'roomid' => $me->roomid,
                'id' => $me->id,
              ]
            )->update(
              [
                'lifelevel' => $me->lifelevel + 1,
              ]
            );
            $parameter['result'] = 'success';
            $parameter['lefelevel']['from'] = $me->lifelevel;
            $parameter['lefelevel']['to'] = $me->lifelevel + 1;
          }
        } else {
          if ($me->lifelevel == 1) {
            $parameter['result'] = 'faild';
            $parameter['lefelevel']['from'] = $me->lifelevel;
            $parameter['lefelevel']['to'] = $me->lifelevel;
          } else {
            DB::table('player')->where(
              [
                'roomid' => $me->roomid,
                'id' => $me->id,
              ]
            )->update(
              [
                'lifelevel' => $me->lifelevel - 1,
              ]
            );
            $parameter['result'] = 'success';
            $parameter['lefelevel']['from'] = $me->lifelevel;
            $parameter['lefelevel']['to'] = $me->lifelevel - 1;
          }
        }
      }

      //history
      DB::table('history')->insert([
        'roomid' => $me->roomid,
        'playerid' => $me->id,
        'turn' => $me->turn,
        'action' => ($isRise ? 'riseLifeLevel' : 'dropLifeLevel'),
        'parameter' => json_encode($parameter),
      ]);
      DB::commit();
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['errors'][] = $ex;
    }
  }

  public static function period($me)
  {
    try {
      //部屋情報
      $room = DB::table('room')->where(
        [
          'id' => $me->roomid,
        ]
      )->get();

      //仕事情報
      $mywork = DB::table('work')->select('vanity', 'lifelevelMax')->where(
        [
          'id' => $me->workid,
        ]
      )->first();

      //資産を棚卸
      $assets = DB::table('asset')->where(
        [
          'roomid' => $me->roomid,
          'playerid' => $me->id,
        ]
      )->where('has', '>', 0)->get();

      $parameter = [];

      //生活水準
      //FIREでなければ職業の上限まで、相応の生活。
      //FIREであれば必ず上げる。
      if ($me->flgFire == 0) {
        $parameter['lifelevel'] = $me->lifelevel + floor($mywork->vanity / 100);
        $rand = rand(1, 100);
        if ($mywork->vanity % 100 > $rand) {
          $parameter['lifelevel']++;
        }

        if ($parameter['lifelevel'] > $mywork->lifelevelMax) {
          $parameter['lifelevel'] = $mywork->lifelevelMax;
        }

        if ($me->lifelevel < $mywork->lifelevelMax) {
          $parameter['lifelevel'] = $parameter['lifelevel'];
        }
      } else {
        $parameter['lifelevel'] = $me->lifelevel;
        if ($me->lifelevel < 10) {
          $parameter['lifelevel']++;
        }
      }

      $parameter['principal'] = 0;
      $parameter['interest'] = 0;
      $parameter['income'] = 0;
      $parameter['rent'] = 0;

      //返済
      $loans = [];
      foreach ($assets as $asset) {
        if ($asset->type == 'loan') {
          $loans[] = $asset;
          $amount = $asset->buy + $asset->sell;
          $principal = $amount / 10;
          $parameter['principal'] += $principal;
          $parameter['interest'] += floor($amount * $asset->return / 100);
        } else if ($asset->type == 'stock') {
          $parameter['income'] += $asset->return * $asset->has;
        } else if ($asset->type == 'estate') {
          $parameter['rent'] += $asset->return * $asset->has;
        }
      }

      //生活費
      $parameter['liveCost'] = 15;
      for ($i = 1; $i <= $me->lifelevel; $i++) {
        $parameter['liveCost'] += $i;
      }
      $parameter['liveCost'] = intVal(pow($parameter['liveCost'], 2) / 2);
      $parameter['total'] = $parameter['income'] +
        $parameter['rent'] -
        $parameter['principal'] -
        $parameter['interest'] -
        $parameter['liveCost'];

      $money = $me->money + $parameter['total'];

      DB::beginTransaction();
      //借金返す
      foreach ($loans as $loan) {
        //元金
        $amount = $loan->buy + $loan->sell;
        $principal = $amount / 10;
        DB::table('asset')->where(
          [
            'id' => $loan->id,
          ]
        )->update(
          [
            'buy' => $loan->buy - $principal,
            'sell' => $loan->sell + $principal,
            'has' => $loan->has - 1
          ]
        );
      }

      //プレイヤーのお金を精算
      DB::table('player')->where(
        [
          'roomid' => $me->roomid,
          'id' => $me->id,
        ]
      )->update(
        [
          'money' => $money,
          'lifelevel' => $parameter['lifelevel'],
        ]
      );

      //history
      DB::table('history')->insert([
        'roomid' => $me->roomid,
        'playerid' => $me->id,
        'turn' => $me->turn,
        'action' => 'periodComplete',
        'parameter' => json_encode($parameter),
      ]);
      DB::commit();
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['errors'][] = $ex;
    }
    return $ret;
  }

  public static function  drowCard($me)
  {
    try {

      //プレイヤーの資産状況を調べる
      $myAssetsCount = DB::table(
        'asset'
      )->where(
        ['roomid' => $me->roomid, 'playerid' => $me->id,]
      )->where(
        'has',
        '>',
        0
      )->where(
        'type',
        '<>',
        'loan'
      )->count();

      while (true) {
        //ランダム変数
        $card = rand(0, 99);
        if ($myAssetsCount == 0 && $card >= 70 && $card < 80) {
        } else {
          break;
        }
      }

      $parameter = [];

      if ($card >= 0 && $card < 40) {
        //働くか、遊ぶか
        $parameter['event'] = 1;
      } else if ($card >= 40 && $card < 55) {
        //株の購入
        $parameter['event'] = 2;
        //購入可能株の発行
        $stock = DB::table('asset')->where(
          [
            'playerid' => $me->id,
            'type' => 'stock',
            'has' => 0,
          ]
        )->delete();

        $price = floor(rand(1, 6)) * 100;
        $return = floor(rand(10, 80) / 1000 * $price);
        DB::table('asset')->insert([
          'roomid' => $me->roomid,
          'playerid' => $me->id,
          'turn' => $me->turn,
          'type' => 'stock',
          'buy' => $price,
          'return' => $return,
        ]);
        $price = floor(rand(7, 13)) * 100;
        $return = floor(rand(15, 90) / 1000 * $price);
        DB::table('asset')->insert([
          'roomid' => $me->roomid,
          'playerid' => $me->id,
          'turn' => $me->turn,
          'type' => 'stock',
          'buy' => $price,
          'return' => $return,
        ]);
        $price = floor(rand(14, 20)) * 100;
        $return = floor(rand(20, 100) / 1000 * $price);
        DB::table('asset')->insert([
          'roomid' => $me->roomid,
          'playerid' => $me->id,
          'turn' => $me->turn,
          'type' => 'stock',
          'buy' => $price,
          'return' => $return,
        ]);
      } else if ($card >= 55 && $card < 70) {
        //不動産の購入
        $parameter['event'] = 3;

        //金利の変動
        $interest = rand(5, 140) / 10;
        $interest = DB::table('room')->where(
          [
            'id' => $me->roomid,
          ]
        )->update(
          [
            'interest' => $interest,
          ]
        );

        //購入可能不動産の発行
        $stock = DB::table('asset')->where(
          [
            'roomid' => $me->roomid,
            'playerid' => $me->id,
            'type' => 'estate',
            'has' => 0,
          ]
        )->delete();
        $price = $me->money * rand(30, 200) / 100;
        $price = floor($price - $price % 100);
        if ($price < 500)
          $price = 500;

        $return = floor(rand(50, 150) / 1000 * $price);
        DB::table('asset')->insert([
          'roomid' => $me->roomid,
          'playerid' => $me->id,
          'turn' => $me->turn,
          'type' => 'estate',
          'buy' => $price,
          'return' => $return,
        ]);
      } else if ($card >= 70 && $card < 80) {
        //資産の売却
        $parameter['event'] = 4;

        //プレイヤーの資産で、手持ちがあり、ローンではないモノを抽出
        $assets = DB::table('asset')->where(
          [
            'roomid' => $me->roomid,
            'playerid' => $me->id,
          ]
        )->where(
          'has',
          '>',
          0
        )->where(
          'type',
          '<>',
          'loan'
        )->get();

        foreach ($assets as $asset) {
          if ($asset->type == 'stock') {
            //売値をランダムで設定（60%～160%の範囲で）
            $sell = intVal($asset->buy * (rand(60, 160)) / 100);
          } else if ($asset->type == 'estate') {
            //売値をランダムで設定（75%～125%の範囲で）
            $sell = intVal($asset->buy * (rand(75, 125)) / 100);
          }
          DB::table('asset')->where(
            [
              'id' => $asset->id,
            ]
          )->update(['sell' => $sell]);
        }
      } else if ($card >= 80 && $card < 95) {
        //生活水準の上げ下げ
        $parameter['event'] = 5;
      } else {
        //贅沢
        $parameter['event'] = 6;
      }

      //history
      DB::table('history')->insert([
        'roomid' => $me->roomid,
        'playerid' => $me->id,
        'turn' => $me->turn,
        'action' => 'drowCard',
        'parameter' => json_encode($parameter),
      ]);
    } catch (\Exception $ex) {
      $ret['code'] = 99;
      $ret['errors'][] = $ex;
    }
  }

  public static function  drowCardFire($me)
  {
    try {

      //プレイヤーの資産状況を調べる
      $myAssetsCount = DB::table(
        'asset'
      )->where(
        ['roomid' => $me->roomid, 'playerid' => $me->id,]
      )->where(
        'has',
        '>',
        0
      )->where(
        'type',
        '<>',
        'loan'
      )->count();

      while (true) {
        //ランダム変数
        $card = rand(0, 99);
        if ($myAssetsCount == 0 && $card >= 70 && $card < 80) {
        } else {
          break;
        }
      }

      $parameter = [];

      if ($card >= 0 && $card < 20) {
        //働くか、遊ぶか
        $parameter['event'] = 1;
      } else if ($card >= 20 && $card < 40) {
        //株の購入
        $parameter['event'] = 2;
        //購入可能株の発行
        $stock = DB::table('asset')->where(
          [
            'playerid' => $me->id,
            'type' => 'stock',
            'has' => 0,
          ]
        )->delete();

        $price = floor(rand(1, 10)) * 100;
        $return = floor(rand(20, 120) / 1000 * $price);
        DB::table('asset')->insert([
          'roomid' => $me->roomid,
          'playerid' => $me->id,
          'turn' => $me->turn,
          'type' => 'stock',
          'buy' => $price,
          'return' => $return,
        ]);
        $price = floor(rand(10, 20)) * 100;
        $return = floor(rand(30, 140) / 1000 * $price);
        DB::table('asset')->insert([
          'roomid' => $me->roomid,
          'playerid' => $me->id,
          'turn' => $me->turn,
          'type' => 'stock',
          'buy' => $price,
          'return' => $return,
        ]);
        $price = floor(rand(20, 50)) * 100;
        $return = floor(rand(40, 180) / 1000 * $price);
        DB::table('asset')->insert([
          'roomid' => $me->roomid,
          'playerid' => $me->id,
          'turn' => $me->turn,
          'type' => 'stock',
          'buy' => $price,
          'return' => $return,
        ]);
      } else if ($card >= 40 && $card < 60) {
        //不動産の購入
        $parameter['event'] = 3;

        //金利の変動
        $interest = rand(5, 140) / 10;
        $interest = DB::table('room')->where(
          [
            'id' => $me->roomid,
          ]
        )->update(
          [
            'interest' => $interest,
          ]
        );

        //購入可能不動産の発行
        $stock = DB::table('asset')->where(
          [
            'roomid' => $me->roomid,
            'playerid' => $me->id,
            'type' => 'estate',
            'has' => 0,
          ]
        )->delete();
        $price = $me->money * rand(40, 120) / 100;
        $price = floor($price - $price % 100);
        if ($price < 2000)
          $price = 2000;

        $return = floor(rand(50, 200) / 1000 * $price);
        DB::table('asset')->insert([
          'roomid' => $me->roomid,
          'playerid' => $me->id,
          'turn' => $me->turn,
          'type' => 'estate',
          'buy' => $price,
          'return' => $return,
        ]);
      } else if ($card >= 60 && $card < 80) {
        //資産の売却
        $parameter['event'] = 4;

        //プレイヤーの資産で、手持ちがあり、ローンではないモノを抽出
        $assets = DB::table('asset')->where(
          [
            'roomid' => $me->roomid,
            'playerid' => $me->id,
          ]
        )->where(
          'has',
          '>',
          0
        )->where(
          'type',
          '<>',
          'loan'
        )->get();

        foreach ($assets as $asset) {
          if ($asset->type == 'stock') {
            //売値をランダムで設定（60%～160%の範囲で）
            $sell = intVal($asset->buy * (rand(60, 160)) / 100);
          } else if ($asset->type == 'estate') {
            //売値をランダムで設定（75%～125%の範囲で）
            $sell = intVal($asset->buy * (rand(75, 125)) / 100);
          }
          DB::table('asset')->where(
            [
              'id' => $asset->id,
            ]
          )->update(['sell' => $sell]);
        }
      } else if ($card >= 80 && $card < 90) {
        //生活水準の上げ下げ
        $parameter['event'] = 5;
      } else {
        //贅沢
        $parameter['event'] = 6;
      }

      //history
      DB::table('history')->insert([
        'roomid' => $me->roomid,
        'playerid' => $me->id,
        'turn' => $me->turn,
        'action' => 'drowCard',
        'parameter' => json_encode($parameter),
      ]);
    } catch (\Exception $ex) {
      $ret['code'] = 99;
      $ret['errors'][] = $ex;
    }
  }

  public static function changeFire($me, $flgFire)
  {
    $ret = [];
    try {

      $parameter = [];
      if ($flgFire == 0) {
        $parameter['action'] = 'dropZone';
      } else if ($flgFire == 1) {
        $parameter['action'] = 'riseZone';
      } else if ($flgFire == 2) {
        $parameter['action'] = 'win';
      }

      //history
      DB::table('history')->insert([
        'roomid' => $me->roomid,
        'playerid' => $me->id,
        'turn' => $me->turn,
        'action' => $parameter['action'],
        'parameter' => json_encode($parameter),
      ]);
    } catch (\Exception $ex) {
      $ret['code'] = 99;
      $ret['errors'][] = $ex;
    }
  }

  public static function jadgeFire(&$me)
  {
    try {
      $assetAmount = 0;
      //資産状況
      $assets = DB::table('asset')->where(
        [
          'roomid' => $me->roomid,
          'playerid' => $me->id,
        ]
      )->where(
        'has',
        '>',
        0
      )->where(
        'type',
        '<>',
        'loan'
      )->get();
      foreach ($assets as $asset) {
        $assetAmount += $asset->buy * $asset->has;
      }

      //仕事情報
      $mywork = DB::table('work')->select('salary')->where(
        [
          'id' => $me->workid,
        ]
      )->first();

      //資産の40倍になったらFIRE / 100倍になったらWIN
      $flgFire = 0;
      if ($me->money > $mywork->salary * 100) {
        $flgFire = 2;
      } else if ($me->money + $assetAmount > $mywork->salary * 40) {
        $flgFire = 1;
      }

      if ($me->flgFire != $flgFire) {
        DB::table('player')->where(
          [
            'id' => $me->id,
          ]
        )->update(['flgFire' => $flgFire]);

        $me = DB::table('player')->select('id', 'roomid', 'turn', 'workid', 'money', 'stress', 'lifelevel', 'flgFire')->where(
          [
            'id' => $me->id,
          ]
        )->first();
      }
    } catch (\Exception $ex) {
      $ret['code'] = 99;
      $ret['errors'][] = $ex;
    }
  }
  */
}
