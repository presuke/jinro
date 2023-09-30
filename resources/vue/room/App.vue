<script>
import axios from 'axios';
import Header from '../Header.vue';
import Footer from '../Footer.vue';

export default {
	components: {
		Header,
		Footer,
	},
	data: () => ({
		isLoading: true,
		modeView: 0,
		rooms: [],
		playersOnRoom: [],
		errors: [],
		form: {
			selection: {
				sex: ['male', 'fmale'],
				imgs: [1,2,3,4,5,6,7,8,9],
				works:[],
			},
			player: {
				id: 0,
				roomid: 0,
				sex: 'male',
				imgSelected: 0,
				img: 0,
				name: '',
				workidSelected: 0,
				workid: 0,
				pass1: '',
				pass2: '',
				color: 'white',
			},
		},
		create: {
			flg: false,
			periodTurnItems: [5, 6, 7, 8, 9, 10],
			playerNumItems: [1, 2, 3, 4, 5],
			form: {
				step: 1,
				roomName: '',
				playerNum: 1,
				periodTurn: 5,
			}, //カンマを追加
			error:'',
		},
	}),
	created: function () {
		this.loadRooms();
		this.loadWorks();
	},
	mounted() {
		window.onload = () => {
			//this.loadRooms();
		};
	},
	methods: {
		loadRooms() {
			this.isLoading = true;
			this.modeView = 0;
			this.form.player.roomid = 0;
			this.errors = [];
			axios
				.get('./api/v1/room/getAll', this.param)
				.then((response) => {
					this.isLoading = false;
					try {
						if(response.data.error != undefined){
							this.errors = response.data.error.errorInfo;
						}else if(response.data.rooms  != undefined){
						//app.rooms = response.data.rooms;
							this.rooms = response.data.rooms;
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
		},
		loadWorks() {
			this.form.selection.works = [];
			this.errors = [];
			axios
				.get('./api/v1/work/getAll', this.param)
				.then((response) => {
					try {
						if(response.data.error != undefined){
							this.errors = response.data.error.errorInfo;
						}else if(response.data.works  != undefined){
						//app.rooms = response.data.rooms;
							this.form.selection.works = response.data.works;
							this.form.selection.works.push({id:0, type:'ランダム', img:'random', salary:'?', lifelevelMin:'?', lifelevelMax:'?' });
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
		},
		prevIcon(){
			if(this.form.player.img == 0){
				this.form.player.img = this.form.selection.imgs.length;
			}else{
				this.form.player.img--;
			}
		},
		nextIcon(){
			if(this.form.player.img == this.form.selection.imgs.length){
				this.form.player.img = 0;
			}else{
				this.form.player.img++;
			}
		},
		prevWork(){
			if(this.form.player.workid == 0){
				this.form.player.workid = this.form.selection.works.length;
			}else{
				this.form.player.workid--;
			}
		},
		nextWork(){
			this.form.player.workid++;
		},
		entryRoom(room){
			this.playersOnRoom = room.players;
			this.form.player.roomid = room.room.id;
			this.modeView = 1;
		},
		playGame(player){
			if(player.name == ''){
				player.sex = 'male';
				player.img = 0;
				player.pass = '';
				this.form.player = player;
				this.modeView = 2;
			}else{
				location.href = './play/' + player.id;
			}
		},
		checkName(){
			this.errors = [];
			if(this.form.player.name == ''){
				this.errors.push('名前を入力してください。')
			}
			else if(this.form.player.name.length > 5){
				this.errors.push('お名前は5文字以内にしてください。');
			}
			if(this.errors.length > 0){

			}
		},
		makePlayer(){
			this.checkName();
			if(this.form.player.pass == ''){
				this.errors.push('パスワードを入力してください。')
			}

			if(this.errors.length == 0){
				this.form.player.img = this.form.selection.imgs[this.form.player.imgSelected];
				this.form.player.workid = this.form.selection.works[this.form.player.workidSelected].id;
				axios
				.post('./api/v1/player/create', this.form.player)
				.then((response) => {
					try {
						if(response.data.player != undefined){
							this.form.player = response.data.player;
							this.form.player.roomid = 0;
							this.modeView = 3;
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
		nextStep() {
			console.log(this.create);
			this.create.error = '';
			if (this.create.form.roomName == '') {
				this.create.error = '部屋名を入力してください。';
			} else if (!this.checkMaxLength(this.create.form.roomName)) {
				this.create.error = '15文字以内で入力ください';
			}

			if (this.create.error == '') {
				this.create.form.step = 2;
			}
		},
		createRoom(){
			this.create.form.step = 3;
			axios
			.post('./api/v1/room/create', {
				parameter: this.create.form,
			})
			.then((response) => {
				if(response.data.code == 0){
					this.create.form.step = 4;
				}else{
					this.create.form.step = 2;
					this.create.error = response.data.error;
				}
			})
			.catch((err) => {
				this.create.error = err.message;
				this.create.form.step = 2;
			});
		},
		returnPage(){

		},
		checkString(inputdata) {
			var regExp = /^[a-zA-Z0-9_]*$/;
			return regExp.test(inputdata);
		},
		checkMaxLength(inputdata) {
			var booleanLength = false;
			inputdata.length <= 15
				? (booleanLength = true)
				: (booleanLength = false);
			return booleanLength;
		},
	},
};
</script>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Kosugi+Maru&family=Rajdhani:wght@500&family=Shrikhand&display=swap');
</style>
<style lang="scss" scoped>
@import '../../scss/app.scss';
</style>
<style>
.step{
	width: 100%;
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
	<header>
		<Header></Header>
	</header>
	<v-card v-bind:class="[create.flg==0 ? 'scaleShow' : 'scaleHide']">
		<div style="margin-top:10px;">
			<v-btn
				@click="loadRooms()"
				>部屋再読み込み</v-btn
			>
			<v-btn
				@click="create.flg = 1"
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
						style="border-radius:5px; padding:5px; background-color:lightgreen; color:white;"
						>
							入室中
						</span>
						<v-btn v-else @click="entryRoom(room)">入室</v-btn>
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
		<Transition name="fade">
			<div v-if="modeView == 1" style="margin:10px;">
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
									:src="'./image/avatar/' + player.sex + '/icon0' + player.img + '.png'" 
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
									:src="'./image/avatar/random.png'" 
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
								<v-btn @click="playGame(player)">
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
		</Transition>

		<!-- setting Player -->
		<Transition name="fade">
			<div v-if="modeView == 2" style="margin:10px;">
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
					<v-btn @click="this.form.player.pass += (n%10) + ''">{{ n%10 }}</v-btn>
				</div>
				<v-spacer></v-spacer>
				<div style="float:left; margin:5px;">
					<v-btn @click="this.form.player.pass = ''">Clear</v-btn>
				</div>
				<br style="clear: both;" />
				<v-select
					label="性別"
					v-model="form.player.sex"
					:items="form.selection.sex"
				></v-select>
				<div style="margin: 10px;">
					アバターアイコンを選択してください。
				</div>
				<div style="width:100%; height:50%; max-height:300px; text-align:center;">
					<v-carousel 
					:continuous="false"
					v-model="form.player.imgSelected"
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
							:src="'./image/avatar/' + form.player.sex + '/icon0' + img + '.png'" 
							class="rounded-circle"
							/>
						</v-carousel-item>
					</v-carousel>
				</div>
				<div style="margin: 10px;">
					仕事を選んで下さい。
				</div>
				<v-carousel
				:continuous="false"
				v-model="form.player.workidSelected"
				show-arrows="true"
				hide-delimiters
				height="200"
				width="400"
				>

					<v-carousel-item
							v-for="work in form.selection.works"
							:key="id"
							style="text-align:center; background-color:red;"
						>
						<v-card
							outlined
							shaped
							style="width:300px; padding:10px; margin: 0 auto;"
						>
							<div style="float:left;">
								<img width="45" height="45" class="rounded-circle" :src="'./image/work/' + work.img + '.png'" />
							</div>
							<div style="float:left;">
								<div class="text-h4">{{work.type}}</div>
							</div>
							<div style="clear:left; margin-left:10px;">
								<div>
									給料：{{ work.salary }}
								</div>
								<div>生活水準</div>
								<ul style="margin-left:10px;">
									<li>
										最低：{{ work.lifelevelMin }}
									</li>
									<li>
										最高：{{ work.lifelevelMax }}
									</li>
								</ul>
							</div>
						</v-card>
					</v-carousel-item>
				</v-carousel>
				<div style="maring-top:10px;" />
				<div style="text-align:center; margin:auto;">
					<v-btn
					class="text-h4"
					elevation="30"
					height="60"
					rounded
					color="deep-purple darken-1"
					@click="makePlayer()">これで決まり！！</v-btn>
				</div>
			</div>
		</Transition>

		<!-- created Player -->
		<Transition name="fade">
			<div v-if="modeView == 3">
				<v-card>
					<v-card-title
					color="primary"
					dark>
					プレイヤー作成完了
					</v-card-title>
					<v-card-text>
						<img 
							:src="'./image/avatar/' + form.player.sex + '/icon0' + form.player.img + '.png'" 
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
		</Transition>

		<!-- Error -->
		<ul class="error">
			<li v-for="error in this.errors">{{ error }}</li>
		</ul>
	</v-card>
	<v-card v-bind:class="[create.flg==1 ? 'scaleShow' : 'scaleHide']"
		style="text-align: center;"
		height="400">
		<div class="step" v-bind:class="[create.form.step==1 ? 'scaleShow' : 'scaleHide']">
			ゲームルームの設定をしてください。
			<div>
				<v-text-field
					id="roomName"
					label="部屋名"
					v-model="create.form.roomName"
					placeholder="部屋名を入力してください"
				>
				</v-text-field>
			</div>
			<div>
				<v-select
					id="playerNum"
					label="プレイヤー数"
					v-model="create.form.playerNum"
					:items="create.playerNumItems"
				></v-select>
			</div>
			<div>
				<v-select
					id="periodTurn"
					label="決算ターン"
					v-model="create.form.periodTurn"
					:items="create.periodTurnItems"
				></v-select>
			</div>
			<v-btn
			@click="create.flg = 0; this.loadRooms();"
			>
			戻る
			</v-btn>
			<v-btn
				@click="nextStep()">
				次へ
			</v-btn>
		</div>
		<div class="step" v-bind:class="[create.form.step==2 ? 'scaleShow' : 'scaleHide']">
			<div>
				これでいいですか？
			</div>
			<div>部屋名:{{ create.form.roomName }}</div>
			<div>プレイヤー数:{{ create.form.playerNum }}</div>
			<div>決算ターン:{{ create.form.periodTurn }}</div>
			<v-btn
				@click="create.form.step=1"
				>考え直す</v-btn>
			<v-btn
				@click="createRoom()"
				>部屋を作成</v-btn>
		</div>
		<div class="step" v-bind:class="[create.form.step==3 ? 'scaleShow' : 'scaleHide']">
			部屋作ってます。ちょっと待ってね。。
		</div>
		<div class="step" v-bind:class="[create.form.step==4 ? 'scaleShow' : 'scaleHide']">
			<div>部屋ができました！</div>
			<div>ボタンを押して、作った部屋でプレイヤーを設定しましょう。</div>
			<v-btn
			@click="create.flg = 0; this.loadRooms();"
			>
			部屋一覧を表示する
			</v-btn>
		</div>
		<div class="error">
			{{ create.error }}
		</div>
	</v-card>
	<footer>
		<Footer></Footer>
	</footer>
</template>
