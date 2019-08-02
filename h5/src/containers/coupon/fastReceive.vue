<template>
    <div class="coupon-receive" v-if="coupons">
        <e-loading v-if="isLoading"></e-loading>
        <div class="coupon-receive-title text-overflow">
            {{settings.name}}
        </div>
        <div class="coupon-receive-ticket">
            <div class='ticket-discount'>
                <span class="rate">{{parseFloat(coupons.rate)}}</span>{{ coupons.type| couponType}}
                <span>优惠券</span>
            </div>
            <div class='ticket-expire text-overflow'>
                <span v-if="coupons.deadlineMode==='day'">领取后{{coupons.fixedDay}}天内有效</span>
                <span v-if="coupons.deadlineMode==='time'">
                    <span v-show="!hasReceive">领券截止日期：</span>
                    <span v-show="hasReceive">优惠券有效至：</span>
                    {{ receiveTimeExpire(coupons.deadline)}}
                </span>
            </div>
            <div class='ticket-range text-overflow'>适用于：{{couponType(coupons)}}</div>
        </div>
        <div class="receive-status" v-if="hasReceive && !loginMethods">
            <img src="static/images/coupon-yes.png" class="status-icon"/>
                <div class="status-text">{{successmessage}}</div>
                <div class="status-user">{{user.nickname}}账户</div>
            <div class="status__btn" @click="useCoupon()">立即使用</div>
        </div>
         <div class="receive-status" v-if="receiveFail && !loginMethods">
            <img src="static/images/coupon-no.png" class="status-icon"/>
            <div class="status-text">{{failmessage}}</div>
        </div>
        <fast-login v-if="!login && !cantuse" @lReceiveCoupon="lReceiveCoupon"></fast-login>
    </div>
</template>
<script>
import fastLogin from './component/fastlogin';
import Api from '@/api';
import { Toast } from 'vant';
import couponMixin from '@/mixins/coupon'
import getCouponMixin from '@/mixins/coupon/getCouponHandler';
import { mapState } from 'vuex';
import { setTimeout } from 'timers';
export default {
    name:"fast-receive",
    components:{
        fastLogin
    },
    mixins: [couponMixin, getCouponMixin],
    data(){
        return{
            coupons:null,//优惠券信息
            login:false,//登录状态
            cantuse:false, //当前优惠券失效了
            hasReceive:false, //是否已经领取了优惠券
            receiveFail:false, //优惠券是否能正常使用
            failmessage:'',
            successmessage:'',
            loginMethods:false, //登录方式 在手机快捷登录的时候是ture
        }
    },
    created(){
        this.getCouponInfo();
    },
    computed: {
        ...mapState({
            user: state => state.user,
            isLoading: state => state.isLoading,
            settings:state => state.settings
        })
    },
    filters: {
        couponType(type){
            if(type=='discount'){
                return '折'
            }
            return '元'
        }
    },
    methods:{
        //通过链接获取优惠券信息
        async getCouponInfo(){
            const token = this.$route.params.token;
            await Api.getCouponInfo({
               query: {
                    batchToken: token
                }
            }).then((res)=>{
                this.coupons=res;
                //是否已经领取过
                if(res.currentUserCoupon){
                    this.hasReceive=true;
                    return 
                }
                //判断优惠券是否已经过期已经是否已经被领完
                let canUseCoupon= this.canUseCoupon(res);
                if(canUseCoupon){
                   this.isLogin();
                }
            }).catch((err)=>{

                Toast.fail(err.message);
            })
        },
        //是否开启云短信
        // async getsettingsCloud(){
        //     await Api.settingsCloud().then(res=>{
        //         //开启了云短信
        //         if(res.sms_enabled==1){
        //             this.cloudSetting=true;
        //         }else{
        //             this.cloudSetting=false;
        //             //跳转登录页
        //             this.toLogin();
        //         }
        //     }).catch(err => {
        //         Toast.fail(err.message)
        //     });
        // },
        //登录后逻辑
        isLogin(){
             if (this.$store.state.token) {
                this.login=true;
                let data={
                    token:this.$route.params.token
                }
                this.newReceiveCoupon(data);
                return;
            }
        },
        //优惠券类型
        couponType(coupons){
            //指定优惠
            if(coupons.target){
                switch (coupons.targetType){
                    case 'course':
                        return `课程/${coupons.target.title}`
                        break;
                    case 'classroom':
                        return `班级/${coupons.target.title}`
                        break;
                    case 'vip':
                        return `会员/${coupons.target.name}`
                        break;
                    default:
                        return ''
                }
            }else{
                //全部
                switch (coupons.targetType){
                    case 'course':
                        return '全部课程'
                        break;
                    case 'classroom':
                        return '全部班级'
                        break;
                    case 'all':
                        return '全部商品'
                        break;
                    case 'vip':
                        return '全部会员'
                        break;
                    default:
                        return ''
                }
            }
        },
        //优惠券引流领取优惠券
        newReceiveCoupon(data,loginReceive){
            Api.pluginsReceiveCoupon({
                data
            }).then((res)=>{
                if(loginReceive){
                    Toast.success('领取成功，正在跳转到详情页...')
                    setTimeout(()=>{
                        this.useCoupon();
                    },3000);  
                    return;
                }else{
                    this.successmessage="领取成功，优惠券已放入"
                    this.hasReceive=true;
                }
            }).catch((err) => {
                if(loginReceive){
                    if(err.code=='5004507'){
                        Toast.success('您已领取过该优惠券，正在跳转到详情页...');
                        setTimeout(()=>{
                            this.useCoupon();
                        },3000);
                    }else{
                        Toast.fail('领取失败，'+err.message);
                    }
                }else{
                    if(err.code=='5004507'){
                        this.successmessage="您已领取过，优惠券已放入"
                        this.hasReceive=true;
                    }else{
                        this.receiveFail=true;
                        this.failmessage='领取失败，'+err.message;
                    }
                }
            });
        },
        //领取优惠券后跳转指定页面
        useCoupon(){
            this.hasreceiveCoupon(this.coupons);
        },
        //登录后领取优惠券
        lReceiveCoupon(user,loginReceive){
            let data={
                token:this.$route.params.token,
            }
            this.loginMethods=true;
            this.newReceiveCoupon(data,true)
        },
        //判断优惠券是否可用
        canUseCoupon(coupon){
            let result=true
            if(coupon.deadline){
                let ONEDAY=86400000;
                let d1=new Date();//取今天的日期
                let d2 = new Date(Date.parse(coupon.deadline));
                if(d1.getTime()>(d2.getTime()+ONEDAY)){
                    this.failmessage="优惠券已过期"
                    this.receiveFail=true;
                    result=false
                }
            }else if(coupon.unreceivedNum==0){
                this.failmessage="优惠券已领完"
                this.receiveFail=true;
                result=false
            }
            this.cantuse=!result;
            return result
        }
    }
}
</script>

