<script>
import axios from 'axios';
import VueQrcode from '@chenfengyuan/vue-qrcode';
import Header from '../Header.vue';
import Footer from '../Footer.vue';
import Const from '../../js/const.js';

export default {
	components: {
		Header,
		Footer,
		VueQrcode,
	},
	data: () => ({
		url: location.href,
		isLoading: true,
		rooms: [],
		playersOnRoom: [],
		errors: [],
		form: {
			selection: {
				sex: ['male', 'fmale'],
				imgs: [0,1,2,3,4,5,6,7,8,9,10,11],
			},
			player: {
				step: 0,
				id: 0,
				roomid: 0,
				sex: 'male',
				img: 0,
				name: '',
				pass: '',
				error:'',
			},
			room: {
				step: 0,
				controll:{
					btnAdd: Const.data.btnAdd,
					btnRemove: Const.data.btnRemove,
				},
				name: '',
				roles:Const.data.roles,
				role: {
					name: '',
					description: '',
				},
				playerNum: 0,
				error: '',
			}, //カンマを追加
		},
		se: Const.data.se,
	}),
	created: function () {
		this.loadRooms();
	},
	mounted() {
		window.onload = () => {
			//this.loadRooms();
		};
	},
	methods: {
		loadRooms() {
			this.isLoading = true;
			this.form.player.step = 0;
			this.form.player.roomid = 0;
			this.errors = [];
			axios
				.get(this.url + '/api/v1/room/getAll', this.param)
				.then((response) => {
					this.isLoading = false;
					try {
						if(response.data.error != undefined){
							this.errors = response.data.error.errorInfo;
						}else if(response.data.rooms  != undefined){
						//app.rooms = response.data.rooms;
							this.rooms = response.data.rooms;
						}else{
							this.errors.push('特定できないエラー');
							console.log(response.data);
						}
					} catch (err) {
						this.errors.push(err);
						console.log(err);
					}
				})
				.catch((err) => {
					this.errors.push(err);
					console.log(err);
				});
		},
		entryRoom(room){
			this.form.player.step = 0;
			setTimeout(()=>{
				this.playersOnRoom = room.players;
				this.form.player.roomid = room.room.id;
				this.form.player.step = 1;
			},100);
		},
		playGame(player){
			if(player.name == ''){
				player.sex = 'male';
				player.img = 0;
				player.pass = '';
				this.form.player = player;
				this.form.player.step = 2;
			}else{
				location.href = '../play/' + player.id;
			}
		},
		createPlayer(){
			this.form.player.error = '';

			if(this.form.player.name == ''){
				this.form.player.error ='名前を入力してください。';
			}
			else if(this.form.player.name.length > 5){
				this.form.player.error ='お名前は5文字以内にしてください。';
			}
			else if(this.form.player.pass == ''){
				this.form.player.error = 'パスワードを入力してください。';
			}else{
				this.playersOnRoom.forEach((player) =>{
					if(player.id != this.form.player.id){
						if(player.name == this.form.player.name){
							this.form.player.error = '名前が他のプレイヤーと重複しています。選び直してください。';
						}
						else if(player.sex == this.form.player.sex && player.img == this.form.player.img){
							this.form.player.error = 'アバターが他のプレイヤーと重複しています。選び直してください。';
						}
					}
				});
			}
			if(this.form.player.error != ''){
				this.se.Error.play();
			}else{
				axios
				.post('../api/v1/player/create', this.form.player)
				.then((response) => {
					try {
						if(response.data.player != undefined){
							this.form.player = response.data.player;
							this.form.player.roomid = 0;
							this.form.player.step = 3;
						}
						else if(response.data.error != undefined){
							this.errors = response.data.error.errorInfo;
						}else{
							this.errors = '特定できないエラー';
						}
					} catch (e) {
						this.errors = e;
					}
				})
				.catch((err) => {
					console.log(err);
				});
			}
		},
		checkCreateRoom() {
			this.changeNum(this.form.room.roles[0], 0);
			if(this.form.room.error == ''){
				if (this.form.room.name == '') {
					this.form.room.error = '部屋名を入力してください。';
				} else if (this.form.room.name > 15) {
					this.form.room.error = '15文字以内で入力ください';
				}

				this.form.room.roles.forEach(role => {
					if(role.id == 1 && role.num == 0){
						this.form.room.error = '人狼は1人以上設定してください';
					}
				});
			}
			if (this.form.room.error == '') {
				this.form.room.step = 2;
			}else{
				this.se.Error.play();
			}
		},
		createRoom(){
			this.form.room.step = 3;
			axios
			.post('../api/v1/room/create', {
				params: this.form.room,
			})
			.then((response) => {
				if(response.data.code == 0){
					this.form.room.step = 4;
					this.se.Success.play();
				}else{
					this.form.room.step = 2;
					this.form.room.error = response.data.error;
					this.se.Error.play();
				}
			})
			.catch((err) => {
				this.form.room.error = err.message;
				this.form.room.step = 2;
				this.se.Error.play();
			});
		},
		showDescription(role){
			this.form.room.role.name = role.name;
			this.form.room.role.description = role.description;
		},
		changeNum(role, num){
			this.se.Push.play();
			if(num > 0 || role.num != 0){
				role.num += num;
			}

			const team = this.countNum();
			if(team.peaple <= team.jinro){
				this.form.room.error = '村人側は人狼側より多くする必要があります。';
			}else{
				this.form.room.error = '';
			}
		},
		countNum(){
			let team = {};
			team.peaple = 0;
			team.jinro = 0;
			this.form.room.roles.forEach(role => {
				if(role.id == 1 || role.id == 5 || role.id == 7
				){
					team.jinro += role.num;
				}else{
					team.peaple += role.num;
				}
			});
			this.form.room.playerNum = team.jinro + team.peaple;
			return team;
		},
		checkString(inputdata) {
			var regExp = /^[a-zA-Z0-9_]*$/;
			return regExp.test(inputdata);
		},
	},
};
</script>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Caveat&family=Reggae+One&display=swap');
</style>
<style lang="scss" scoped>
@import '../../scss/app.scss';
</style>
<style>
#header{
  position: -webkit-sticky;
  position: sticky;
  top: 0;
  z-index: 9;
}
.step{
	width: 100%;
}
.inRoom{
	border-radius:5px;
	padding:5px;
	background-color:rgb(230, 253, 230); 
	text-shadow: 
        0 0 10px green,
        0 0 20px lightgreen;
	box-shadow: 
        0 0 10px lightgreen,
        0 0 20px yellowgreen,
        0 0 40px yellow,
        0 0 80px gold;
	color:rgb(0, 187, 0);
}
.role{
	position: relative;
	width:90px;
	height:150px;
	border-radius: 10px;
	background-size: contain;
	margin:3px;
	.num {
		position:absolute;
		right:0;
		width:25px;
		height:25px;
		background-color: rgba(255, 0, 0, 0.5);
		border-radius: 50%;
	}
	.name{
		position:absolute;
		left:0;
		right:0;
		text-align:center;
		background-color: #333;
		bottom:30px;
	}
	.btnAdd{
		position:absolute;
		right:5px;
		bottom:45px;
		width:25px;
		height: 25px;
		z-index:2;
	}
	.btnRemove{
		position:absolute;
		left:5px;
		bottom:45px;
		width:25px;
		height: 25px;
		z-index:2;
	}
}
.scaleShow{
	transition: all 0.5s 0s ease;
	zoom:1.0;
}
.scaleHide{
	transition: all 0.5s 0s ease;
	zoom:1.0;
	transform:scale(0.0, 1.0);
	transform-origin: 0px 0px;
	position: absolute;
	transform-origin-y: 0px;
}
.error {
	font-size: smaller;
	color: red;
}
</style>
<template>
	<div style="  max-width: 1024px; margin: 0 auto; font-family: 'Reggae One', cursive;">
		<div id="header">
			<Header></Header>
		</div>
		<v-card 
		style="text-align: center; margin-bottom:20px;"
		>
			<div v-bind:class="[form.room.step==0 ? 'scaleShow' : 'scaleHide']">
				<div style="margin-top:10px;">
					<v-btn
					color="purple darken-4"
					@click="se.Push.play(); loadRooms();"
					>
					部屋再読み込み
					</v-btn>
					<v-btn
					color="purple darken-4"
					@click="se.Push.play(); form.room.step=1;"
					>
						部屋を作成する
					</v-btn>
				</div>
				<v-container>
					<!-- 部屋一覧-->
					<v-row v-for="room in rooms" :key="id" v-if="!isLoading">
						<v-col
							style="
								padding: 10px;
								border-bottom: solid thin gray;
							"
						>
							<div style="float: left">
								{{ room.room.name }}
							</div>
							<div style="float: right;">
								<span
								v-if="this.form.player.roomid == room.room.id"
								class="inRoom"
								>
									入室中
								</span>
								<v-btn
								color="purple darken-4"
								v-else @click="entryRoom(room)"
								>
									入室
								</v-btn>
							</div>
							<br style="clear: left" />
						</v-col>
					</v-row>
					<div v-if="isLoading">
						<v-progress-circular color="blue-lighten-3" model-value="20" :size="77"></v-progress-circular>
						loading data...
					</div>
				</v-container>

				<!-- Entry Room -->
				<div v-bind:class="[form.player.step==1 ? 'scaleShow' : 'scaleHide']"
				style="margin:10px;"
				>
					<v-container>
						<v-row style="margin: 0 10;">
							プレイするプレイヤーを選んでください。
						</v-row>
						<v-row v-for="player in playersOnRoom" :key="id">
							<v-col
								style="
									border-bottom:solid thin lightgray;
									padding: 10px;
								"
							>
								<div v-if="player.name != ''">
									<div style="float: left;">
										<img 
										:src="'../image/avatar/' + player.sex + '/icon0' + player.img + '.png'" 
										class="rounded-circle"
										Width="30"
										Height="30"
										/>
									</div>
									<div style="float: left;">
										{{ player.name }}&nbsp;
									</div>
								</div>
								<div v-else>
									<div style="float: left;">
										<img 
										:src ="'../image/avatar/random.png'" 
										class="rounded-circle"
										Width="30"
										Height="30"
										/>
									</div>
									<div style="float: left;">
										プレイヤー未作成
									</div>
								</div>
								<div
									style="
										float: right;
									"
								>
									<v-btn
										@click="playGame(player)"
										color="purple darken-4"
										>
										<span v-if="player.name != ''">
											参加
										</span>
										<span v-else>
											プレイヤー作成
										</span>
									</v-btn>
								</div>
								<br style="clear: left" />
							</v-col>
						</v-row>
					</v-container>
				</div>

				<!-- create player -->
				<div v-bind:class="[form.player.step==2 ? 'scaleShow' : 'scaleHide']" 
				style="margin:10px;"
				>
					<div>
						プレイヤーの設定をしてください。
					</div>
					<v-text-field
						label="お名前"
						v-model="this.form.player.name"
						v-on:change="checkName"
						placeholder="お名前を入力してください。"
					>
					</v-text-field>
					<v-text-field
						label="パスワード"
						type="password"
						v-model="this.form.player.pass"
						hint="Enter your password to access this website"
					></v-text-field>
					<div v-for="n in 10" style="float:left; margin:5px;">
						<v-btn 
						color="purple darken-4"
						@click="this.form.player.pass += (n%10) + ''"
						>
						{{ n%10 }}
						</v-btn>
					</div>
					<v-spacer></v-spacer>
					<div style="float:left; margin:5px;">
						<v-btn 
						color="purple darken-4"
						@click="this.form.player.pass = ''"
						>
						Clear
						</v-btn>
					</div>
					<br style="clear: both;" />
					<v-select
						label="性別"
						v-model="form.player.sex"
						:items="this.form.selection.sex"
					></v-select>
					<div style="margin: 10px;">
						アバターアイコンを選択してください。
					</div>
					<div style="width:100%; height:50%; max-height:300px; text-align:center;">
						<v-carousel 
						:continuous="false"
						v-model="form.player.img"
						width="200"
						height="200"
						class="rounded-circle"
						hide-delimiters
						>
							<v-carousel-item
								v-for="img in form.selection.imgs"
								contain
							>
								<img 
								:src="'../image/avatar/' + form.player.sex + '/icon0' + img + '.png'" 
								class="rounded-circle"
								/>
							</v-carousel-item>
						</v-carousel>
					</div>
					<div style="maring-top:10px;">
						<div class="error">
							{{ form.player.error }}
						</div>
					</div>
					<div style="text-align:center; margin:auto;">
						<v-btn
						class="text-h4"
						elevation="30"
						height="60"
						rounded
						color="deep-purple darken-1"
						@click="this.createPlayer()">これで決まり！！</v-btn>
					</div>
				</div>

				<!-- created Player -->
				<div  v-bind:class="[form.player.step==3 ? 'scaleShow' : 'scaleHide']"
				style="margin-top:10px;"
				>
					<v-card>
						<v-card-title
						color="primary"
						dark>
						プレイヤー作成完了
						</v-card-title>
						<v-card-text>
							<img 
								:src="'../image/avatar/' + form.player.sex + '/icon0' + form.player.img + '.png'" 
								class="rounded-circle"
								/>
							<div>
								{{this.form.player.name}}さん、ようこそ！
							</div>
						</v-card-text>
						<v-card-actions>
							<v-spacer></v-spacer>
							<v-btn @click="playGame(this.form.player)">
								ゲームに参加
							</v-btn>
						</v-card-actions>
					</v-card>
				</div>
			</div>
			<!--create room-->
			<div class="step" v-bind:class="[form.room.step==1 ? 'scaleShow' : 'scaleHide']">
				ゲームルームの設定をしてください。
				<div>
					<v-text-field
						id="roomName"
						label="部屋名"
						v-model="form.room.name"
						placeholder="部屋名を入力してください"
					>
					</v-text-field>
				</div>
				<div style="text-align: center;">
					各役割の人数を設定してください。
					<div>
						現在のプレイヤー数：{{ this.form.room.playerNum }}
					</div>

					<div v-for="role in form.room.roles" 
					style="margin:0 auto; width:min(80vw, 600px);"
					>
						<div style="float:left;">
							<div 
							class="role"
							:style="{ backgroundImage: `url('${role.img}')` }"
							@click="this.showDescription(role)"
							>
								<div class="num">
									{{ role.num }}
								</div>
								<img 
								class="btnRemove"
								:src="form.room.controll.btnRemove" 
								@click="changeNum(role, -1)" />
								<img 
								class="btnAdd"
								:src="form.room.controll.btnAdd" 
								@click="changeNum(role, 1)" />
								<div class="name">
									{{ role.name }}
								</div>
							</div>
						</div>
					</div>
				</div>
				<div 
				v-bind:class="[form.room.role.name!='' ? 'scaleShow' : 'scaleHide']"
				style="clear:both; border-radius: 5px; border: solid 1px yellow; padding:5px; margin:10px;"
				>
					<div>
						役割説明　【{{ this.form.room.role.name }}】
					</div>
					<div style="margin: 10px;">
						{{ this.form.room.role.description }}
					</div>
				</div>
				<br  style="clear:left;" />
				<div class="error">
					{{ form.room.error }}
				</div>
				<div>
					<v-btn
					color="purple darken-4"
					@click="se.Push.play(); form.room.step = 0; this.loadRooms();"
					>
					戻る
					</v-btn>
					<v-btn
					color="purple darken-4"
					@click="se.Push.play(); this.checkCreateRoom()"
					>
					次へ
					</v-btn>
				</div>
			</div>
			<div class="step" v-bind:class="[form.room.step==2 ? 'scaleShow' : 'scaleHide']">
				<div>
					これでいいですか？
				</div>
				<div>部屋名:{{ form.room.name }}</div>
				<div style="text-align: center;">
					<div v-for="role in form.room.roles" 
					style="margin:0 auto; width:min(80vw, 600px);"
					>
						<div style="float:left;">
							<div 
							class="role"
							:style="{ backgroundImage: `url('${role.img}')` }"
							>
								<div class="num">
									{{ role.num }}
								</div>
								<div class="name">
									{{ role.name }}
								</div>
							</div>
						</div>
					</div>
				</div>
				<br  style="clear:left;" />
				<div class="error">
					{{ form.room.error }}
				</div>
				<div>
					<v-btn
					color="purple darken-4"
					@click="se.Push.play(); form.room.step=1;"
					>
					考え直す
					</v-btn>
					<v-btn
					color="purple darken-4"
					@click="se.Push.play(); createRoom();"
					>
					部屋を作成
					</v-btn>
				</div>
			</div>
			<div class="step" v-bind:class="[form.room.step==3 ? 'scaleShow' : 'scaleHide']">
				部屋作ってます。ちょっと待ってね。。
			</div>
			<div class="step" v-bind:class="[form.room.step==4 ? 'scaleShow' : 'scaleHide']">
				<div>部屋ができました！</div>
				<div>ボタンを押して、作った部屋でプレイヤーを設定しましょう。</div>
				<v-btn
				color="purple darken-4"
				@click="se.Push.play(); form.room.step=0; this.loadRooms();"
				>
				部屋一覧表示
				</v-btn>
			</div>
		</v-card>
		<VueQrcode :value="url" :options="{ width: 200 }" />
		<footer>
			<Footer></Footer>
		</footer>
	</div>
</template>
