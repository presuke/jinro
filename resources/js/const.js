import BtnAdd from '../image/control/circle-add.svg';
import BtnRemove from '../image/control/circle-remove.svg';
import ImgC0 from '../image/c0.jpg';
import ImgC1 from '../image/c1.jpg';
import ImgC2 from '../image/c2.jpg';
import ImgC3 from '../image/c3.jpg';
import ImgC4 from '../image/c4.jpg';
import ImgC5 from '../image/c5.jpg';
import ImgC6 from '../image/c6.jpg';
import ImgC7 from '../image/c7.jpg';
import ImgC8 from '../image/c8.jpg';
import ImgC9 from '../image/c9.jpg';
import SePush from '../se/push.mp3';
import SeSuccess from '../se/success.mp3';
import SeError from '../se/error.mp3';
import SeJail from '../se/jail.mp3';
import SeWin from '../se/win.mp3';
import SeConsider from '../se/consider.mp3';
import SeSafe from '../se/safe.mp3';
import SeFreedom from '../se/freedom.mp3';
import SeJailAndFreedom from '../se/jailandfreedom.mp3';

export default {
	data:{
        roles: [
            {
                id: 0,
                name:'村人',
                img: ImgC0,
                color: 'gray',
                num: 0,
                command:[]
            },
            {
                id: 1,
                name:'人狼',
                img: ImgC1,
                color: 'red',
                num: 0,
                command:[]
            },
            {
                id: 2,
                name:'狩人',
                img: ImgC2,
                color: 'green',
                num: 0,
                command:[]
            },
            {
                id: 3,
                name:'占い師',
                img: ImgC3,
                color: 'cyan',
                num: 0,
                command:[]
            },
            {
                id: 4,
                name:'霊媒師',
                img: ImgC4,
                color: 'magenta',
                num: 0,
                command:[]
            },
            {
                id: 5,
                name:'裏切者',
                img: ImgC5,
                color: 'orange',
                num: 0,
                command:[]
            },
            {
                id: 6,
                name:'神',
                img: ImgC6,
                color: 'gold',
                num: 0,
                command:[]
            },
            {
                id: 7,
                name:'吸血鬼',
                img: ImgC7,
                color: 'purple',
                num: 0,
                command:[]
            },
            {
                id: 8,
                name:'増税眼鏡',
                img: ImgC8,
                color: 'darkyellow',
                num: 0,
                command:[]
            },
            {
                id: 9,
                name:'天使',
                img: ImgC9,
                color: 'lightyellow',
                num: 0,
                command:[]
            },
        ],
        actions:{
            vote: 'vote',
            attack: 'attack',
            save: 'save',
            sleep: 'sleep',
            predict: 'predict',
            expose: 'expose',
            consider: 'consider',
            revive: 'revive',
            change: 'change',
            freedom: 'freedom',
            confirmVoteResult: 'confirmVoteResult',
            confirmActionResult: 'confirmActionResult',
            confirmFreedomResult: 'confirmFreedomResult',
        },
        times:{
            0: '夕刻',
            1: '夜',
            2: '深夜',
            3: '朝',
            4: '昼',
        },
        btnAdd: BtnAdd,
        btnRemove: BtnRemove,
        se:{
            Success: new Audio(SeSuccess),
            Error: new Audio(SeError),
            Push: new Audio(SePush),
            Jail: new Audio(SeJail),
            Win: new Audio(SeWin),
            Consider:new Audio(SeConsider),
            Safe:new Audio(SeSafe),
            Freedom:new Audio(SeFreedom),
            JailAndFreedom:new Audio(SeJailAndFreedom),
        }
	},
    methods:{
        getAssetPath(file){
            return new URL(`../assets/${file}`, import.meta.url).href
        },
    }
};