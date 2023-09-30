<template>
    <x-sign id="title">The Cash Flow</x-sign>
    <!--
    <v-toolbar fluid density="comfortable">
		プレイルーム
		<v-btn @click="logout()">ログアウト</v-btn>
		{{ $parent.problem }}
	</v-toolbar>
    -->
</template>
<style>
    #title {
        background-color:darkred;
        position:flex;
    }

    x-sign {
        --interval: 1s;
        display: block;
        text-shadow: 
        0 0 10px var(--color1),
        0 0 20px var(--color2),
        0 0 40px var(--color3),
        0 0 80px var(--color4);
        will-change: filter, color;
        filter: saturate(60%);
        animation: flicker steps(100) var(--interval) 1s infinite;
    }
    x-sign:nth-of-type(1) {
        color: lightyellow;
        --color1: yellow;
        --color2: gold;
        --color3: orange;
        --color4: darkred;
        font-family: 'Shrikhand', cursive;
        font-size: min(10vw, 40px);
        font-weight: 700;
    }
</style>