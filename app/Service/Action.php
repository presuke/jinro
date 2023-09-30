<?php

namespace App\Service;

use DB;

class Action
{
  public static function confirm($me, $crntPlayer)
  {
    $ret = [];

    try {
      $parameter = [];
      //誰のどのターンに対するConfirmか
      $parameter['crntPlayerRoomid'] = $crntPlayer['roomid'];
      $parameter['crntPlayerid'] = $crntPlayer['id'];
      $parameter['crntPlayerTurn'] = $crntPlayer['turn'];


      //既にConfirm済みか？
      $existsMyConfirm = DB::table('history')->where(
        [
          'roomid' => $me->roomid,
          'playerid' => $me->id,
          'turn' => $me->turn,
          'action' => 'confirm',
          'parameter' => json_encode($parameter)
        ]
      )->count();

      DB::beginTransaction();
      //confirm情報がなければ、historyへInsert
      if ($existsMyConfirm == 0) {
        DB::table('history')->insert([
          'roomid' => $me->roomid,
          'playerid' => $me->id,
          'turn' => $me->turn,
          'action' => 'confirm',
          'parameter' => json_encode($parameter)
        ]);
      }

      //部屋の人数
      $playerNum = DB::table('player')->where([
        'roomid' => $me->roomid,
      ])->count();

      //confirmした人の人数
      $confirmedPlayerNum = DB::table('history')->where([
        'roomid' => $me->roomid,
        'action' => 'confirm',
        'parameter' => json_encode($parameter)
      ])->count();

      //全員confirm済かどうか
      $existsAllConfirm = DB::table('history')->where([
        'roomid' => $me->roomid,
        'action' => 'confirmAll',
        'parameter' => json_encode($parameter)
      ])->count();

      //全員確認したらターンを進める
      if (
        $playerNum == $confirmedPlayerNum &&
        $existsAllConfirm == 0
      ) {

        //資産の推移を記録
        $crntPlayersAssets = DB::table('asset')->select(
          'buy',
          'type',
          'has'
        )->where(
          ['roomid' => $crntPlayer['roomid'], 'playerid' => $crntPlayer['id']]
        )->get();

        $amountStock = 0;
        $amountEstate = 0;
        $amountLoan = 0;
        $amountMoney = 0;
        foreach ($crntPlayersAssets as $asset) {
          if ($asset->type == 'stock') {
            $amountStock += $asset->buy * $asset->has;
          } else if ($asset->type == 'estate') {
            $amountEstate += $asset->buy * $asset->has;
          } else if ($asset0 > type == 'loan') {
            $amountLoan += $asset->buy * $asset->has;
          }
        }

        $player = DB::table('player')->select('money')->where([
          'roomid' => $crntPlayer['roomid'],
          'id' => $crntPlayer['id'],
        ])->first();
        $amountMoney = $player->money;

        $existsTrans = DB::table('trans')->where([
          'roomid' => $crntPlayer['roomid'],
          'id' => $crntPlayer['id'],
          'turn' => $crntPlayer['turn'],
        ])->count();

        if ($existsTrans == 0) {
          DB::table('trans')->insert([
            'roomid' => $crntPlayer['roomid'],
            'playerid' => $crntPlayer['id'],
            'turn' => $crntPlayer['turn'],
            'money' => $amountMoney,
            'stock' => $amountStock,
            'estate' => $amountEstate,
            'loan' => $amountLoan,
          ]);
        } else {
          DB::table('trans')->where([
            'roomid' => $crntPlayer['roomid'],
            'id' => $crntPlayer['id'],
            'turn' => $crntPlayer['turn'],
          ])->update([
            'money' => $amountMoney,
            'stock' => $amountStock,
            'estate' => $amountEstate,
            'loan' => $amountLoan,
          ]);
        }

        DB::table('player')->where([
          'roomid' => $crntPlayer['roomid'],
          'id' => $crntPlayer['id'],
        ])->update([
          'turn' => $crntPlayer['turn'] + 1,
        ]);

        //history
        DB::table('history')->insert([
          'roomid' => $crntPlayer['roomid'],
          'playerid' => $crntPlayer['id'],
          'turn' => $crntPlayer['turn'],
          'action' => 'confirmAll',
          'parameter' => json_encode($parameter),
        ]);
      } else {
        $ret['message'] = '確認しました。他のプレイヤーが確認するの待ちましょう。';
      }
      DB::commit();
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function  work($me)
  {
    $ret = [];
    try {
      //ストレスチェック
      if ($me->stress > 10) {
        //ランダム変数
        $sic = (microtime(true) * 1000);
        $sic = $sic % 10;
        if ($sic > 5) {
          $ret = self::treat($me, true);
          return $ret;
        }
      }

      //ランダム変数
      $stress = (microtime(true) * 1000);
      $stress = $stress % 3 + 1;

      $mywork = DB::table('work')->select('salary')->where(
        [
          'id' => $me->workid,
        ]
      )->first();

      $parameter = [];
      $parameter['stress'] = $stress;
      $parameter['money'] = $mywork->salary;

      DB::beginTransaction();
      $player = DB::table('player')->where(
        [
          'roomid' => $me->roomid,
          'id' => $me->id,
        ]
      )->update(
        [
          'money' => $me->money + $mywork->salary,
          'stress' => $me->stress + $stress,
        ]
      );
      //history
      DB::table('history')->insert([
        'roomid' => $me->roomid,
        'playerid' => $me->id,
        'turn' => $me->turn,
        'action' => 'work',
        'parameter' => json_encode($parameter),
      ]);
      DB::commit();
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function  treat($me, $isSic = false)
  {
    $ret = [];
    try {
      //ランダム変数
      $recover = (microtime(true) * 1000);
      $recover = $recover % 5 + 2;

      $cost = $isSic ? 12 : 10;
      $payment = $recover * $cost * $me->lifelevel;

      if ($recover > $me->stress) {
        $stress = 0;
      } else {
        $stress = $me->stress - $recover;
      }

      $parameter = [];
      $parameter['money'] = $payment * -1;
      $parameter['stress'] = $recover * -1;

      DB::beginTransaction();
      $player = DB::table('player')->where(
        [
          'roomid' => $me->roomid,
          'id' => $me->id,
        ]
      )->update(
        [
          'money' => $me->money - $payment,
          'stress' => $stress,
        ]
      );
      //history
      $action = $isSic ? 'sic' : 'treat';
      DB::table('history')->insert([
        'roomid' => $me->roomid,
        'playerid' => $me->id,
        'turn' => $me->turn,
        'action' => $action,
        'parameter' => json_encode($parameter),
      ]);
      DB::commit();
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['code'] = 99;
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function banking($me, $amount)
  {
    $ret = [];
    try {

      $myloansThisTurn = DB::table('asset')->where(
        [
          'playerid' => $me->id,
          'turn' => $me->turn,
          'type' => 'loan',
        ]
      )->count();
      if ($myloansThisTurn > 0) {
        $ret['error'] = 'このターンではもう借入できません。';
        return $ret;
      }


      $limitOfSalary = 10;
      $mywork = DB::table('work')->select('salary')->where(
        [
          'id' => $me->workid,
        ]
      )->first();

      $myloans = DB::table('asset')->where(
        [
          'playerid' => $me->id,
          'type' => 'loan',
        ]
      )->get();

      $myloanTotal = 0;
      foreach ($myloans as $myloan) {
        $myloanTotal += $myloan->buy;
      }

      if ($amount > $mywork->salary * $limitOfSalary - $myloanTotal) {
        if ($mywork->salary * $limitOfSalary - $myloanTotal > 0) {
          $ret['error'] = '借入限度額は、' . number_format($mywork->salary * $limitOfSalary - $myloanTotal) . 'までです。';
        } else {
          $ret['error'] = 'これ以上借り入れることはできません。';
        }
      } else {
        $room = DB::table('room')->select('interest')->where(
          [
            'id' => $me->roomid,
          ]
        )->first();

        $history = DB::table('history')->select('roomid', 'playerid', 'action', 'parameter')->where(
          [
            'roomid' => $me->roomid,
            'playerid' => $me->id,
          ]
        )->orderBy('ins', 'DESC')->first();

        $parameter = [];
        foreach (json_decode($history->parameter) as $key => $value) {
          $parameter[$key] = $value;
        }
        $parameter['loan'] = $amount;
        $parameter['moneyBefore'] = $me->money;

        DB::beginTransaction();
        //asset
        DB::table('asset')->insert([
          'roomid' => $me->roomid,
          'playerid' => $me->id,
          'turn' => $me->turn,
          'type' => 'loan',
          'buy' => $amount,
          'has' => 10,
          'return' => $room->interest,
        ]);

        $player = DB::table('player')->where(
          [
            'roomid' => $me->roomid,
            'id' => $me->id,
          ]
        )->update(
          [
            'money' => $me->money + $amount,
          ]
        );
        //history
        DB::table('history')->insert([
          'roomid' => $me->roomid,
          'playerid' => $me->id,
          'turn' => $me->turn,
          'action' => 'loan',
          'parameter' => json_encode($parameter),
        ]);
        DB::commit();
        $ret['message'] = number_format($amount) . 'の借入に成功しました。';
      }
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function buyEstate($me)
  {
    $ret = [];
    try {
      $myestate = DB::table('asset')->select('id', 'buy')->where(
        [
          'playerid' => $me->id,
          'turn' => $me->turn,
          'type' => 'estate',
          'has' => 0,
        ]
      )->first();

      if ($myestate->buy > $me->money) {
        $ret['error'] = number_format($myestate->buy) . 'の物件を購入するお金がありません。';
      } else {
        $room = DB::table('room')->select('interest')->where(
          [
            'id' => $me->roomid,
          ]
        )->first();

        $parameter = [];
        $parameter['buyEstate'] = $myestate;
        $parameter['moneyBefore'] = $me->money;
        $parameter['money'] = $myestate->buy * -1;
        $parameter['estate'] = $myestate->buy;

        DB::beginTransaction();
        //asset
        DB::table('asset')->where(['id' => $myestate->id,])->update(['has' => 1]);

        $player = DB::table('player')->where(
          [
            'roomid' => $me->roomid,
            'id' => $me->id,
          ]
        )->update(
          [
            'money' => $me->money - $myestate->buy,
          ]
        );
        //history
        DB::table('history')->insert([
          'roomid' => $me->roomid,
          'playerid' => $me->id,
          'turn' => $me->turn,
          'action' => 'buyEstate',
          'parameter' => json_encode($parameter),
        ]);
        DB::commit();
        $ret['message'] = '物件の購入に成功しました。';
      }
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function buyStock($me, $params)
  {
    $ret = [];
    try {

      $totalAmount = 0;
      $assets = $params['assets'];
      foreach ($assets as $asset) {
        if (
          $asset['playerid'] == $me->id &&
          $asset['turn'] == $me->turn &&
          $asset['has'] > 0
        ) {
          $assetRecord = DB::table('asset')->select('buy')->where(['id' => $asset['id']])->first();
          $totalAmount += $assetRecord->buy * $asset['has'];
        }
      }

      if ($totalAmount == 0) {
        $ret['error'] = '購入する株券が指定されていません。';
        return $ret;
      }

      if ($totalAmount > $me->money) {
        $ret['error'] = '所持金が少なくて買えませんでした。(' . number_format($totalAmount) . '必要)';
        return $ret;
      }

      DB::beginTransaction();
      //asset
      foreach ($assets as $asset) {
        if ($asset['playerid'] == $me->id && $asset['has'] > 0) {
          DB::table('asset')->where(
            [
              'id' => $asset['id'],
            ]
          )->update(
            [
              'has' => $asset['has'],
            ]
          );
        }
      }

      $player = DB::table('player')->where(
        [
          'roomid' => $me->roomid,
          'id' => $me->id,
        ]
      )->update(
        [
          'money' => $me->money - $totalAmount,
        ]
      );
      //history
      $parameter = [];
      $parameter['money'] = -1 * $totalAmount;
      $parameter['stock'] = $totalAmount;
      DB::table('history')->insert([
        'roomid' => $me->roomid,
        'playerid' => $me->id,
        'turn' => $me->turn,
        'action' => 'buyStock',
        'parameter' => json_encode($parameter),
      ]);
      DB::commit();
      $ret['message'] = '合計' . number_format($totalAmount) . 'の株式投資をしました。';
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

  public static function trade($me, $params)
  {
    $ret = [];
    try {

      $totalSellStock = 0;
      $totalBuyStock = 0;
      $totalSellEstate = 0;
      $totalBuyEstate = 0;
      $assetRecords = DB::table('asset')->select('id', 'playerid', 'type', 'buy', 'sell', 'has')->where(['playerid' => $me->id])->get();
      $assets = $params['assets'];
      $lstAssetSells = [];
      foreach ($assets as $asset) {
        if ($asset['trade'] == 'true') {
          foreach ($assetRecords as $assetRecord) {
            if (
              $assetRecord->has > 0 &&
              $asset['id'] == $assetRecord->id
            ) {
              if ($assetRecord->type == 'stock') {
                $totalSellStock += $assetRecord->sell * $assetRecord->has;
                $totalBuyStock += $assetRecord->buy * $assetRecord->has;
                $lstAssetSells[] = $assetRecord->id;
              } else if ($assetRecord->type == 'estate') {
                $totalSellEstate += $assetRecord->sell * $assetRecord->has;
                $totalBuyEstate += $assetRecord->buy * $assetRecord->has;
                $lstAssetSells[] = $assetRecord->id;
              }
            }
          }
        }
      }

      if (count($lstAssetSells) == 0) {
        $ret['error'] = '売却する資産が指定されていません。';
        return $ret;
      }

      DB::beginTransaction();
      //asset
      foreach ($lstAssetSells as $assetid) {
        DB::table('asset')->where(
          [
            'id' => $assetid,
          ]
        )->update(
          [
            'has' => 0,
          ]
        );
      }

      $player = DB::table('player')->where(
        [
          'roomid' => $me->roomid,
          'id' => $me->id,
        ]
      )->update(
        [
          'money' => $me->money + $totalSellStock + $totalSellEstate,
        ]
      );
      //history
      $parameter = [];
      $parameter['money'] = $totalSellStock + $totalSellEstate;
      $parameter['stock'] = $totalBuyStock * -1;
      $parameter['estate'] = $totalBuyEstate * -1;
      $parameter['profit'] = $totalSellStock + $totalSellEstate - $totalBuyStock - $totalBuyEstate;
      DB::table('history')->insert([
        'roomid' => $me->roomid,
        'playerid' => $me->id,
        'turn' => $me->turn,
        'action' => 'trade',
        'parameter' => json_encode($parameter),
      ]);
      DB::commit();
      $ret['message'] = '合計' . number_format($totalSellStock + $totalSellEstate) . 'の資産売却をしました。';
    } catch (\Exception $ex) {
      DB::rollback();
      $ret['error'] = $ex->getMessage();
    }
    return $ret;
  }

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
}
