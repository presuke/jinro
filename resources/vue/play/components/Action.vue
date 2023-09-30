<style lang="scss" scoped>
@import '../../../scss/play/action.scss';
</style>
<template>
    <!--ユーザ未登録-->
    <v-card 
    v-bind:class="[$parent.problem == $parent.const.problems.namelessMyself ? 'scaleShow' : 'scaleHide']">
        <v-card-title
        color="primary"
        dark
        >
        <span class="text-h5">ユーザ未登録</span>
        </v-card-title>
        <v-card-text>
            このユーザは未登録です。まずユーザ登録を済ませてください。
        </v-card-text>
        <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
            color="blue-darken-1"
            variant="text"
            href="/room/"
            >
                戻る
            </v-btn>
        </v-card-actions>
    </v-card>
    <!--他プレイヤー未登録あり-->
    <v-card 
    v-bind:class="[$parent.problem == $parent.const.problems.namelessOtherPlayer ? 'scaleShow' : 'scaleHide']">
        <v-card-title
        color="primary"
        dark
        >
        <span class="text-h5">未登録ユーザあり</span>
        </v-card-title>
        <v-card-text>
            この部屋のプレイヤーで登録が済んでない人がいます。もうちょっと待ちましょう。
        </v-card-text>
        <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
            color="blue-darken-1"
            @click="$parent.problem = $parent.const.problems.unKnown; $parent.refleshStatus()">
                再読み込み
            </v-btn>
        </v-card-actions>
    </v-card>
    <div
    v-bind:class="[$parent.problem == $parent.const.problems.noProblem ? 'scaleShow' : 'scaleHide']"
    id="actionMain"
    height="300">
        <v-progress-linear 
            v-model="$parent.reflesh.countValue"
            color="red">
            <!-- {{ $parent.reflesh.count }}/ {{ $parent.reflesh.const.countMax }} -->
            </v-progress-linear>
            <v-card-title>
                <div class="crentPlayer">
                    <img 
                    :src="$parent.const.docPath + '/image/avatar/' + $parent.crntPlayer.sex + '/icon0' + $parent.crntPlayer.img + '.png'" 
                    class="rounded-circle"
                    style="width:30px; height:30px;	vertical-align:middle;"
                    />
                    {{ $parent.crntPlayer.name }}さんの番です。
                </div>
        </v-card-title>
        <!-- card area -->
        <div
        class="action">
            <div v-bind:class="[$parent.action.event == 0 ? 'slideDown' : 'slideUp']"
            style="color:white; text-align:center;" 
            >
                <div v-if="($parent.crntPlayer.turn % $parent.room.period) == 0 && $parent.action.action != 'periodComplete'"
                class="periodComplete"
                >
                    <div style="background-image: url('../../../image/action/kessan.png'); background-repeat: no-repeat; background-position-x: center; background-size: contain; width:300px;height:200px;">
                        <div style="font-size:100px; color:yellow; text-shadow: 0 0 10px red, 0 0 20px darkred;">
                            決算
                        </div>
                        <div v-if="$parent.crntPlayer.id == $parent.me.id">
                            <v-btn @click="$parent.act('period')">
                                決算処理をする
                            </v-btn>
                        </div>
                    </div>
                </div>
                <div v-else-if="$parent.action.event == 0"
                style="margin: 0 auto; width:420px;"
                >
                    カードを選んでください。
                    <div style="text-align:center;">
                        <div style="margin: 0 auto; width:100%;">
                            <div v-for="n of 6" :key="n">
                                <v-card 
                                class="cardDesign" 
                                @click="$parent.act('drowCard')"></v-card>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <v-card v-bind:class="[$parent.action.event == 1 ? 'slideDown' : 'slideUp']"
            class="cardPanel" 
            >
                <div class="frame"></div>
                <div class="actionTitle">働く？休む？</div>
                <div style="margin: 0 auto;">
                    <div class="actionControll" style="top:50px; left:0px; right:0px; margin:auto; width:90%;">
                        <div 
                        class="cardDesign workMale"
                        v-if="$parent.crntPlayer.sex == 'male'"
                        @click="$parent.act('work')">
                            働く
                        </div>
                        <div 
                        class="cardDesign workFmale"
                        v-else
                        @click="$parent.act('work')">
                            働く
                        </div>
                        <div 
                        class="cardDesign treat"
                        @click="$parent.act('treat')">
                            休む
                        </div>
                    </div>
                </div>
            </v-card>
            <v-card v-bind:class="[$parent.action.event == 2 ? 'slideDown' : 'slideUp']"
            class="cardPanel" 
            styel="text-align:center"
            >
                <div class="frame"></div>
                <div class="actionTitle">株を購入することができる</div>
                <div style="margin: 38 auto 0; width:330px;">
                    <div v-for="asset in $parent.assets" >
                        <div v-if="asset.playerid  == $parent.crntPlayer.id && asset.turn == $parent.crntPlayer.turn && asset.type=='stock'">
                            <div style="float:left;">
                                <div class="stock">
                                    <div style="font-size:smaller; text-align:left; text-shadow: 1px 2px 3px #C0C0C0; color:white;">
                                        価格：{{ asset.buy.toLocaleString() }}
                                    </div>
                                    <div style="margin-top:-5px; font-size:smaller; text-align:left; text-shadow: 1px 2px 3px #C0C0C0; color:white;">
                                        配当：{{ Math.trunc(asset.return).toLocaleString() }}
                                    </div>
                                    <div style="margin-top:-5px; font-size:smaller; text-align:left; text-shadow: 1px 2px 3px #C0C0C0; color:white;">
                                        利回：{{ (asset.return * 100 / asset.buy).toFixed(1) }}%
                                    </div>
                                </div>
                                <div style="position:relative; z-index:9; margin-top:10px; text-shadow: 2 2 0.2 lightgray; font-family: 'Shrikhand', cursive;;">
                                    <img 
                                    :src="$parent.const.docPath + '/image/control/circle-remove.svg'" 
                                    class="stockAdjust"
                                    @click="$parent.changeStockHas(asset, -1)" />
                                    {{ asset.has }}
                                    <img 
                                    :src="$parent.const.docPath + '/image/control/circle-add.svg'" 
                                    class="stockAdjust"
                                    @click="$parent.changeStockHas(asset, 1)" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="clear:left;">
                    <div style="color:red; font-size:smaller; padding:5px; background-color: rgba(255, 255, 255, 0.5);">
                        <div>*資産売却カードを引いたとき売却できます。</div>
                        <div>*決算ごとに配当を得られます。</div>
                    </div>
                    <div class="actionControll" v-if="$parent.me.id  == $parent.crntPlayer.id">
                        <v-btn @click="$parent.act('buyStock')">
                            買う
                        </v-btn>
                        <v-btn @click="$parent.act('lostStock')">
                            見送る
                        </v-btn>
                    </div>
                </div>
            </v-card>
            <v-card v-bind:class="[$parent.action.event == 3 ? 'slideDown' : 'slideUp']"
            class="cardPanel" 
            >
                <div class="frame"></div>
                <div class="actionTitle">不動産を購入できる</div>
                <div v-for="asset in $parent.assets" >
                    <div v-if="asset.playerid  == $parent.crntPlayer.id && asset.has == 0 && asset.type == 'estate'"
                    class="estate">
                        <div style="background-color: rgba(255, 255, 255, 0.5);">
                            <div>
                                価格：{{ asset.buy.toLocaleString() }}
                            </div>
                            <div>
                                利回：{{ (asset.return *100 / asset.buy).toFixed(1) }}％
                            </div>
                        </div>
                        <div class="actionControll">
                            <v-btn @click="$parent.doBanking(asset.buy)">
                            借入
                            </v-btn>
                            <v-btn @click="$parent.act('buyEstate')">
                                買う
                            </v-btn>
                            <v-btn @click="$parent.act('lostEstate')">
                                見送る
                            </v-btn>
                        </div>
                    </div>
                </div>
            </v-card>
            <v-card v-bind:class="[$parent.action.event == 4 ? 'slideDown' : 'slideUp']"
            class="cardPanel trade" 
            >
                <div class="frame"></div>
                <div class="actionTitle">資産を売却することができる</div>
                <div class="assetlist">
                    <div
                    v-for="asset in $parent.assets" >
                        <div 
                        v-if="asset.playerid  == $parent.crntPlayer.id && asset.has > 0 && asset.type != 'loan'"
                        >
                            <div style="clear:left; float:left; width:20px;">
                                <img 
                                :src="$parent.const.docPath + '/image/status/' + asset.type + '.svg'" 
                                style="width:20px; height:20px;"
                                />
                            </div>
                            <div style="float:left; width:60px; text-align:right;">
                                {{ asset.buy.toLocaleString() }}
                            </div>
                            <div style="float:left;">
                                ⇒
                            </div>
                            <div  style="float:left;width:60px; text-align:right;">
                                {{ asset.sell.toLocaleString() }}
                            </div>
                            <div style="float:left;">
                                ×
                            </div>
                            <div  style="float:left;width:40px; text-align:right;">
                                <div v-if="asset.type == 'stock'">
                                {{ asset.has}}枚
                                </div>
                                <div v-else>
                                {{ asset.has}}件
                                </div>
                            </div>
                            <div style="float:left; margin-top:5px;">
                                <input type="checkbox"
                                v-model="asset.trade" 
                                style="position: relative; z-index:2;"
                                @click="asset.trade = !asset.trade"
                                />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="actionControll">
                    <div styel="clear:left;">
                        <v-btn @click="$parent.act('trade')">
                            売却する
                        </v-btn>
                        <v-btn @click="$parent.act('noTrade')">
                            見送る
                        </v-btn>
                    </div>
                </div>
            </v-card>
            <v-card v-bind:class="[$parent.action.event == 5 ? 'slideDown' : 'slideUp']"
            class="cardPanel" 
            >
                <div class="frame"></div>
                <div class="actionTitle">
                    生活水準の見直し
                </div>
                <div class="actionControll" style="top:50px; left:0px; right:0px; margin:auto; width:90%;">
                    <div 
                        class="cardDesign lifelevelRise"
                        @click="$parent.act('riseLifeLevel')">
                        上げる
                        </div>
                        <div 
                        class="cardDesign lifelevelDrop"
                        @click="$parent.act('dropLifeLevel')">
                        下げる
                        </div>
                </div>
            </v-card>
            <v-card v-bind:class="[$parent.action.event == 6 ? 'slideDown' : 'slideUp']"
            class="cardPanel" 
            >
                <div class="frame"></div>
                <div class="actionTitle">休暇を取得してリフレッシュする</div>
                <div class="vacation">
                    <img 
                    :src="$parent.const.docPath + '/image/action/vacation.png'" />
                </div>
                <div class="actionControll">
                    <v-btn @click="$parent.act('treat')">
                        遊ぶ
                    </v-btn>
                </div>
            </v-card>
            <v-card v-bind:class="[$parent.action.event == 9 ? 'slideDown' : 'slideUp']"
            class="cardPanel" 
            >
                Error:Event9
            </v-card>
            <div v-bind:class="[$parent.action.event == 99 ? 'slideDown' : 'slideUp']"
            class="cardPanel actionResult" 
            >
            <div class="frame"></div>
                <div v-if="$parent.action.action == 'work'"
                class="work"
                >
                    <div 
                    v-if="$parent.crntPlayer.stress < 10"
                    >
                        <div class="actionTitle">一生懸命働きました！</div>
                        <img
                        :src="$parent.const.docPath + '/image/action/salary_' + $parent.crntPlayer.sex + '.png'"
                        style="width: 220px; height: 220px;"
                        />
                    </div>
                    <div 
                    v-else
                    >
                        <div class="actionTitle">一生懸命働きましたが、<br />休まないと病気になるかもしれません。</div>
                        <img
                        :src="$parent.const.docPath + '/image/action/work_hard_' + $parent.crntPlayer.sex + '.png'"
                        style="width: 200px; height: 200px;"
                        />
                    </div>
                    <div class="statusChange" style="margin:0 auto;">
                        <div>
                            <img 
                            src="../../../image/status/money.svg" 
                            class="icon" 
                            style="filter: drop-shadow(2px 2px 2px #990);"
                            />
                            +{{ $parent.action.parameter.money }}
                        </div>
                        <div>
                            <img 
                            src="../../../image/status/health.svg" 
                            class="icon" 
                            style="filter: drop-shadow(2px 2px 2px #c66);"
                            />
                            +{{ $parent.action.parameter.stress }}
                        </div>
                    </div>
                </div>
                <div v-else-if="$parent.action.action == 'sic'"
                class="sic"
                >
                    <div class="actionTitle">
                        <div>過労で倒れてしまいました！</div>
                        <div>治療費を払いました。</div>
                    </div>
                    <img
                    :src="$parent.const.docPath + '/image/action/sic_' + $parent.crntPlayer.sex + '.png'"
                    style="width: 250px; height: 250px;"
                    />
                    <div class="statusChange" style="margin:0 auto;">
                        <div>
                            <img 
                            src="../../../image/status/money.svg" 
                            class="icon" 
                            style="filter: drop-shadow(2px 2px 2px #990);"
                            />
                            {{ $parent.action.parameter.money }}
                        </div>
                        <div>
                            <img 
                            src="../../../image/status/health.svg" 
                            class="icon" 
                            style="filter: drop-shadow(2px 2px 2px #c66);"
                            />
                            {{ $parent.action.parameter.stress }}
                        </div>
                    </div>
                </div>
                <div v-else-if="$parent.action.action == 'treat'"
                class="vacation"
                >
                    <div 
                    v-if="Math.ceil($parent.crntPlayer.lifelevel /2) == 1"
                    >
                        <div class="actionTitle">ゲームで遊びまくりました！</div>
                        <img :src="$parent.const.docPath + '/image/action/v1.png'" />
                    </div>
                    <div 
                    v-else-if="Math.ceil($parent.crntPlayer.lifelevel /2) == 2"
                    >
                        <div class="actionTitle">カラオケパーティーで大はしゃぎしました！</div>
                        <img :src="$parent.const.docPath + '/image/action/v2.jpg'" />
                    </div>
                    <div 
                    v-else-if="Math.ceil($parent.crntPlayer.lifelevel /2) == 3"
                    >
                        <div class="actionTitle">
                            温泉旅行でリフレッシュしました！
                        </div>
                        <img :src="$parent.const.docPath + '/image/action/v3.jpg'" />
                    </div>
                    <div 
                    v-else-if="Math.ceil($parent.crntPlayer.lifelevel /2) == 4"
                    >
                        <div class="actionTitle">豪華ホテルでディナー！</div>
                        <img :src="$parent.const.docPath + '/image/action/v4.jpg'" />
                    </div>
                    <div 
                    v-else-if="Math.ceil($parent.crntPlayer.lifelevel /2) == 5"
                    >
                        <div class="actionTitle">海外旅行で大豪遊！</div>
                        <img :src="$parent.const.docPath + '/image/action/v5.jpg'" />
                    </div>
                    <div class="statusChange" style="margin:0 auto;">
                        <div>
                            <img 
                            src="../../../image/status/money.svg" 
                            class="icon" 
                            style="filter: drop-shadow(2px 2px 2px #990);"
                            />
                            {{ $parent.action.parameter.money }}
                        </div>
                        <div>
                            <img 
                            src="../../../image/status/health.svg" 
                            class="icon" 
                            style="filter: drop-shadow(2px 2px 2px #c66);"
                            />
                            {{ $parent.action.parameter.stress }}
                        </div>
                    </div>
                </div>
                <div v-else-if="$parent.action.action == 'riseLifeLevel'">
                    <div v-if="$parent.action.parameter.result == 'success'">
                        <div class="actionTitle">生活水準を上げました</div>
                        <div style="margin-top:30px;">
                            <img src="../../../image/action/lifelevelRise.png" style="width:200px; height:150px;" />
                        </div>
                    </div>
                    <div v-else>
                        <div class="actionTitle">生活水準を上げようとしましたが、思いとどまりました。</div>
                        <div style="margin-top:70px;">
                            <img src="../../../image/action/lifelevelRise.png" style="width:140px; height:112px; filter:grayscale(100%);" />
                        </div>
                    </div>
                </div>
                <div v-else-if="$parent.action.action == 'dropLifeLevel'">
                    <div v-if="$parent.action.parameter.result == 'success'">
                        <div class="actionTitle">生活水準を下げました</div>
                        <div style="margin-top:30px;">
                            <img src="../../../image/action/lifelevelDrop.png" style="width:200px; height:150px;" />
                        </div>
                    </div>
                    <div v-else>
                        <div class="actionTitle">生活水準を下げようとしましたが、思いとどまりました。</div>
                        <div style="margin-top:70px;">
                            <img src="../../../image/action/lifelevelDrop.png" style="width:140px; height:112px; filter:grayscale(100%);" />
                        </div>
                    </div>
                </div>
                <div v-else-if="$parent.action.action == 'buyStock'"
                class="buyStock"
                >
                    <div class="actionTitle">株式投資をしました。</div>
                    <img 
                        :src="$parent.const.docPath + '/image/action/stock_buy_' + $parent.crntPlayer.sex + '.png'"
                        style="width:200px; height:200px;"
                        />
                    <div class="statusChange" style="margin:0 auto;">
                        <div>
                            <img 
                            src="../../../image/status/money.svg" 
                            class="icon" 
                            style="filter: drop-shadow(2px 2px 2px #990);"
                            />
                            {{ $parent.action.parameter.money.toLocaleString() }}
                        </div>
                        <div>
                            <img 
                            src="../../../image/status/stock.svg" 
                            class="icon" 
                            style="filter: drop-shadow(2px 2px 2px #c66);"
                            />
                            +{{ $parent.action.parameter.stock.toLocaleString() }}
                        </div>
                    </div>
                </div>
                <div v-else-if="$parent.action.action == 'buyEstate'">
                    <div>
                        <div class="actionTitle">不動産物件を購入しました。</div>
                        <img 
                            :src="$parent.const.docPath + '/image/action/estate_buy_' + $parent.crntPlayer.sex + '.png'"
                            style="margin-top:10px; width:200px; height:180px;"
                            />
                    </div>
                    <div class="statusChange" style="margin:0 auto;">
                        <div>
                            <img 
                            src="../../../image/status/money.svg" 
                            class="icon" 
                            style="filter: drop-shadow(2px 2px 2px #990);"
                            />
                            {{ $parent.action.parameter.money.toLocaleString() }}
                        </div>
                        <div>
                            <img 
                            src="../../../image/status/estate.svg" 
                            class="icon" 
                            style="filter: drop-shadow(2px 2px 2px #c66);"
                            />
                            +{{ $parent.action.parameter.estate.toLocaleString() }}
                        </div>
                    </div>
                </div>
                <div v-else-if="$parent.action.action == 'noTrade'">
                    <div class="actionTitle">資産の売却を見送りました。</div>
                    <div class="lost">
                        <img 
                        :src="$parent.const.docPath + '/image/action/lost_' + $parent.crntPlayer.sex + '.png'" />
                    </div>
                </div>
                <div v-else-if="$parent.action.action == 'trade'"
                class="trade"
                >
                    <div 
                    v-bind:class="[$parent.crntPlayer.sex == 'male' ? 'male' : 'fmale']" 
                    >
                    <div class="actionTitle">資産を売却しました。</div>
                    </div>
                    <div class="statusChange" style="transform:scale(0.6,0.6); margin-top:-20px;">
                        <div>
                            <img 
                            src="../../../image/status/money.svg" 
                            class="icon" 
                            style="filter: drop-shadow(2px 2px 2px #990);"
                            />
                            {{ $parent.action.parameter.money }}
                        </div>
                        <div>
                            <img 
                            src="../../../image/status/stock.svg" 
                            class="icon" 
                            style="filter: drop-shadow(2px 2px 2px #66c);"
                            />
                            {{ $parent.action.parameter.stock }}
                        </div>
                        <div>
                            <img 
                            src="../../../image/status/estate.svg" 
                            class="icon" 
                            style="filter: drop-shadow(2px 2px 2px #6c6);"
                            />
                            {{ $parent.action.parameter.estate }}
                        </div>
                    </div>
                </div>
                <div v-else-if="$parent.action.action == 'lostEstate'">
                    <div class="actionTitle">不動産物件の購入を見送りました。</div>
                    <div class="lost">
                        <img 
                        :src="$parent.const.docPath + '/image/action/lost_' + $parent.crntPlayer.sex + '.png'" />
                    </div>
                </div>
                <div v-else-if="$parent.action.action == 'lostStock'">
                    <div class="actionTitle">株券の購入を見送りました。</div>
                    <div class="lost">
                        <img 
                        :src="$parent.const.docPath + '/image/action/lost_' + $parent.crntPlayer.sex + '.png'" />
                    </div>
                </div>
                <div v-else-if="$parent.action.action == 'periodComplete'"
                class="periodComplete">
                    <div class="sheet">
                        決算書
                        <div class="row">
                            <div>生活費</div>
                            <div style="color:red;">
                                {{ ($parent.action.parameter.liveCost * -1).toLocaleString() }}
                            </div>
                        </div>
                        <div class="row">
                            <div>借入元本返済</div>
                            <div style="color:red;">
                                {{ ($parent.action.parameter.principal * -1).toLocaleString() }}
                            </div>
                        </div>
                        <div class="row">
                            <div>借入利子</div>
                            <div style="color:red;">
                                {{ ($parent.action.parameter.interest * -1).toLocaleString() }}
                            </div>
                        </div>
                        <div class="row">
                            <div>株の配当</div>
                            <div>
                                {{ ($parent.action.parameter.income).toLocaleString() }}
                            </div>
                        </div>
                        <div class="row">
                            <div>家賃収入</div>
                            <div>
                                {{ ($parent.action.parameter.rent).toLocaleString() }}
                            </div>
                        </div>
                        <div class="row">
                            <div>収支</div>
                            <div v-if="$parent.action.parameter.total >= 0">
                                +{{ ($parent.action.parameter.total).toLocaleString() }}
                            </div>
                            <div v-else style="color:red;">
                                {{ ($parent.action.parameter.total).toLocaleString() }}
                            </div>
                        </div>
                    </div>
                    <br style="clear: left;" />
                </div>
                <div v-else-if="$parent.action.action == 'riseZone'"
                class="riseZone"
                >
                    <div>
                        <div class="actionTitle">FireZoneに突入しました！</div>
                        <img
                        :src="$parent.const.docPath + '/image/action/riseFire.jpg'"
                        style="margin-top:30px; width: 320px; height: 180px;"
                        />
                    </div>
                </div>
                <div v-else-if="$parent.action.action == 'dropZone'"
                class="dropZone"
                >
                    <div>
                        <div class="actionTitle">ラットレースゾーンに転落しました！</div>
                        <img
                        :src="$parent.const.docPath + '/image/action/dropFire_' + $parent.crntPlayer.sex + '.png'"
                        style="margin-top:20px; width: 200px; height: 180px;"
                        />
                    </div>
                </div>
                <div v-else-if="$parent.action.action == 'win'"
                class="win"
                >
                    <div class="actionTitle">ゲームセット！</div>
                    <div>
                        <div class="winner">
                            現時点でFIREZONEのプレイヤーの勝利です！
                            <div v-for="player in $parent.players" >
                                <div v-if="player.flgFire >= 1">
                                    <img 
                                    :src="$parent.const.docPath + '/image/avatar/' + player.sex + '/icon0' + player.img + '.png'" 
                                    class="rounded-circle icon" 
                                    style="width:min(6vw, 30px); height:min(6vw, 30px); vertical-align: middle;" 
                                    />
                                    {{ player.name }}さん
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="actionControll"
                v-if="$parent.action.action != 'win'">
                    <v-btn class="actionBtn" @click="$parent.confirm()">
                        確認
                    </v-btn>
                </div>
            </div>
            <div class="message">{{ $parent.actionResult.message }}</div>
            <div class="error">{{ $parent.actionResult.error }}</div>
        </div>
        <div v-bind:class="[$parent.isProcessing ? 'scaleShow' : 'scaleHide']" 
        id="processing"
        >
            <v-progress-circular indeterminate />Processing.. {{ $parent.ret }}
        </div>
    </div>
</template>