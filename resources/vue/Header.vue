<script>
import logo from '../image/logo.png';

export default {
    data: function () {
        return {
            logo: logo
        }
    }
}
</script>
<template>
    <div class="logo">
        <img :src="logo" />
    </div>
    <x-sign id="title">
        人狼
    </x-sign>
</template>
<style>
    #title {
        background-color:#333;
    }
    .logo {
        position:absolute;
        top:5;
        left:5;
        z-index:1;
    }
    .logo img{
        width:80px; 
        height:80px;
        filter: drop-shadow(0px 0px 15px red);
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
        --color1: red;
        --color2: red;
        --color3: red;
        --color4: pink;
        font-size: min(10vw, 40px);
        font-weight: 700;
        font-family: 'Reggae One', cursive;
    }
</style>