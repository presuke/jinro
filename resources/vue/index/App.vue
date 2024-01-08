<script>
import Header from '../Header.vue';
import Footer from '../Footer.vue';

export default {
	components: {
		Header,
		Footer,

	},
	data: () => ({
		form:{
			copyright:{
				displayDialog: false,
			},
		},
	}),
};
</script>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Kosugi+Maru&family=Rajdhani:wght@500&family=Shrikhand&display=swap');
</style>
<style lang="scss" scoped>
@import '../../scss/app.scss';
</style>
<style>
.btn,
a.btn,
button.btn {
  font-size: 1.6rem;
  font-weight: 700;
  line-height: 1.5;
  position: relative;
  display: inline-block;
  padding: 1rem 4rem;
  cursor: pointer;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  -webkit-transition: all 0.3s;
  transition: all 0.3s;
  text-align: center;
  vertical-align: middle;
  text-decoration: none;
  letter-spacing: 0.1em;
  color: #212529;
  border-radius: 0.5rem;
}

.btn-border-gradient-wrap {
  display: inline-block;

  padding: 0.2rem;

  border-radius: 0.5rem;
}

.btn-border-gradient-wrap--gold {
  background-image: -webkit-linear-gradient(
    315deg,
    #704308 0%,
    #ffce08 37%,
    #fefeb2 47%,
    #fafad6 50%,
    #fefeb2 53%,
    #e1ce08 63%,
    #704308 100%
  );
  background-image: linear-gradient(
    135deg,
    #704308 0%,
    #ffce08 37%,
    #fefeb2 47%,
    #fafad6 50%,
    #fefeb2 53%,
    #e1ce08 63%,
    #704308 100%
  );
}

.btn-border-gradient-wrap--gold:hover a.btn {
  text-shadow: 0 0 15px rgba(250, 250, 214, 0.5),
    0 0 15px rgba(250, 250, 214, 0.5), 0 0 15px rgba(250, 250, 214, 0.5),
    0 0 15px rgba(250, 250, 214, 0.5);
}

a.btn-border-gradient {
  font-size: 2rem;
  background: #000;
}

.btn-text-gradient--gold {
  font-family: 'Shrikhand', cursive;

  background: -webkit-gradient(
    linear,
    left bottom,
    left top,
    from(#ffffdb),
    to(#a16422)
  );

  background: -webkit-linear-gradient(bottom, #ffffdb, #a16422);

  background: linear-gradient(to top, #ffffdb, #a16422);
  -webkit-background-clip: text;

  -webkit-text-fill-color: transparent;
}
</style>

<template>
	<header>
		<Header></Header>
	</header>
	<div class="area">
		<div style="text-align: center;">
			<div style="margin:auto; max-width:500px; max-height:auto; position: relative; z-index:10;">
				<img src="image/top.png" style="width:100%; height:auto;" />
				<div class="btn-border-gradient-wrap btn-border-gradient-wrap--gold">
					<a href="./room/" class="btn btn-border-gradient"><span class="btn-text-gradient--gold">Play Game</span></a>
				</div>
			</div>
		</div>
		<ul class="circles" style="z-index: 0;">
				<li>&yen;</li>
				<li>$</li>
				<li>€</li>
				<li>&yen;</li>
				<li>$</li>
				<li>€</li>
				<li>&yen;</li>
				<li>$</li>
				<li>€</li>
				<li>&yen;</li>
		</ul>
	</div>
	<!--Notifyダイアログ-->
	<v-dialog 
	v-model="this.form.copyright.displayDialog"
	transition="dialog-top-transition"
	max-width="400"
	>
		<v-card width="320" height="400">
			<v-card-text style="overflow-y: auto;">
				<div style="font-size:smaller;">
					<h2>素材提供</h2>
					<ul style="margin-left:20px;">
						<li>
							<a href="https://blog.goo.ne.jp/akarise" target="_blank">ゆうひな様</a>
						</li>
						<li>
							ステータスアイコン<a href="https://icooon-mono.com/" target="_blank">モノアイコン</a>
						</li>
						<li>
							アクションイラスト：<a href="https://www.irasutoya.com/" target="_blank">いらすとや様</a>
						</li>
						<li>
							効果音：<a href="https://soundeffect-lab.info/" target="_blank">効果音ラボ様</a>
						</li>
						<li>
							画像生成AI：<a href="https://www.bing.com/images/create?FORM=GDPCLS" target="_blank">Bing Image Creator</a>
						</li>
					</ul>
					<div style="font-size:smaller;">
						※素敵な素材、ツールのご提供に、感謝して使わせていただいております。
					</div>
					<h2>ソース</h2>
					<div>
						<img 
						src="../../image/github-mark.svg" 
						class="icon" 
						style="filter: drop-shadow(2px 2px 2px #66c);width:30px;height:30px;"
						/>
						<a href="https://github.com/renoneve/cashflow" target="_blank">
							https://github.com/renoneve/cashflow
						</a>
						<div style="font-size:smaller;">
							※vue+laravel勉強目的で作成したものです。非効率な記述や細かな不具合などあるかもしれません。
						</div>
					</div>
				</div>
			</v-card-text>
			<v-card-actions>
				<v-spacer></v-spacer>
				<v-btn
				color="blue-darken-1"
				variant="text"
				@click="this.form.copyright.displayDialog = false;"
				>
					閉じる
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
	<footer @click="this.form.copyright.displayDialog = true;">
		<Footer></Footer>
	</footer>
</template>
