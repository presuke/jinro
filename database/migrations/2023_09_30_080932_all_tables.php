<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('room');
        Schema::dropIfExists('player');
        Schema::dropIfExists('history');
        Schema::dropIfExists('infomation');

        Schema::create('room', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('部屋名')->unique();
            $table->integer('day')->default(1)->comment('何日目か');
            $table->integer('time')->default(0)->comment('時間帯（夕方：投票、夜：投票結果発表、深夜：攻防、朝：攻防結果発表）');
            $table->integer('win')->default(0)->comment('どちらが勝ったか（1：村人、2：人狼）');
            $table->string('roles', 1024)->default('')->comment('この部屋に存在する役割');
            $table->datetime('ins')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->datetime('upd')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
        });

        Schema::create('player', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('roomid')->comment('部屋ID');
            $table->string('name')->default('')->comment('プレイヤー名');
            $table->string('pass')->default('')->comment('パスワード');
            $table->string('sex')->default('')->comment('性別');
            $table->integer('img')->default(0)->comment('キャラクターイメージ');
            $table->integer('role')->default(0)->comment('役割（0：村人、1：人狼、2：狩人、3：占い師、4：霊媒師、5：裏切者、6：神）');
            $table->integer('flgDead')->default(0)->comment('退場フラグ');
            $table->datetime('ins')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->datetime('upd')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
        });

        Schema::create('history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('roomid')->comment('部屋ID');
            $table->integer('day')->comment('何日目か');
            $table->integer('time')->comment('時間帯（夕方：投票、夜：投票結果発表、深夜：攻防、朝：攻防結果発表）');
            $table->integer('playerid')->comment('プレイヤーID');
            $table->integer('targetid')->comment('アクション対象プレイヤーID');
            $table->string('action')->comment('アクション');
            $table->datetime('ins')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->datetime('upd')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
        });

        Schema::create('infomation', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('roomid')->comment('部屋ID');
            $table->integer('day')->comment('何日目か');
            $table->integer('time')->comment('時間帯（夕方：投票、夜：投票結果発表、深夜：攻防、朝：攻防結果発表）');
            $table->string('parameter')->default('')->comment('パラメータ（JSON形式）');
            $table->datetime('ins')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('作成日時');
            $table->datetime('upd')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'))->comment('更新日時');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('room');
        Schema::dropIfExists('player');
        Schema::dropIfExists('history');
        Schema::dropIfExists('infomation');
    }
};
