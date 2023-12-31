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
                description:'特殊能力を持ちません。',
                img: ImgC0,
                color: 'gray',
                num: 0,
                command:[]
            },
            {
                id: 1,
                name:'人狼',
                description:'襲撃対象を投獄することができます。別の人狼が襲撃したとき、誰が襲撃されたか知ることができます。（狩人に守られると襲撃が失敗します。）常に、人狼と裏切り者と吸血鬼のプレイヤーを把握できます。',
                img: ImgC1,
                color: 'red',
                num: 0,
                command:[]
            },
            {
                id: 2,
                name:'狩人',
                description:'指定したプレイヤーを人狼の襲撃から守ることができます。自分を守ることや2日連続で同じプレイヤーを守ることはできません。',
                img: ImgC2,
                color: 'green',
                num: 0,
                command:[]
            },
            {
                id: 3,
                name:'占い師',
                description:'指定したキャラクターの正体を見破ることができます。ただし、裏切り者を占うと村人だと判定してしまいます。',
                img: ImgC3,
                color: 'cyan',
                num: 0,
                command:[]
            },
            {
                id: 4,
                name:'霊媒師',
                description:'投票で投獄されたキャラクターの正体を見破ることができます。',
                img: ImgC4,
                color: 'magenta',
                num: 0,
                command:[]
            },
            {
                id: 5,
                name:'裏切者',
                description:'特殊な能力はありませんが、人狼が勝利すると勝利します。',
                img: ImgC5,
                color: 'orange',
                num: 0,
                command:[]
            },
            {
                id: 6,
                name:'神',
                description:'常に、全てのキャラクターの正体を把握することができます。',
                img: ImgC6,
                color: 'gold',
                num: 0,
                command:[]
            },
            {
                id: 7,
                name:'吸血鬼',
                description:'襲撃すると襲撃対象と役割が入れ替わります。（襲撃対象が投獄されることはありません。）人狼が勝利すると勝利します。',
                img: ImgC7,
                color: 'purple',
                num: 0,
                command:[]
            },
            {
                id: 8,
                name:'増税眼鏡',
                description:'検討すると50％の確率で声を出してしまいます。また50％の確率で国会へ逃げ込んで人狼の襲撃から保身します。',
                img: ImgC8,
                color: 'darkyellow',
                num: 0,
                command:[]
            },
            {
                id: 9,
                name:'天使',
                description:'投獄されたプレイヤーを救出することができます。',
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