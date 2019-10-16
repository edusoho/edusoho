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
                <span v-if="coupons.deadlineMode==='day'">
                    <span v-if="currentUserCoupon!=null">
                       优惠券有效至：{{ receiveTimeExpire(currentUserCoupon.deadline)}}
                    </span>
                    <span  v-if="currentUserCoupon==null">
                       领取后{{coupons.fixedDay}}天内有效
                    </span>
                </span>
                <span v-if="coupons.deadlineMode==='time'">
                    <span v-show="!hasReceive">领券截止日期：</span>
                    <span v-show="hasReceive">优惠券有效至：</span>
                    {{ receiveTimeExpire(coupons.deadline)}}
                </span>
            </div>
            <div class='ticket-range text-overflow'>适用于：{{couponType(coupons)}}</div>
        </div>
        <div class="receive-status" v-if="hasReceive && !loginMethods && couponSwitch">
            <img src="static/images/coupon-yes.png" class="status-icon"/>
                <div class="status-text">{{successmessage}}</div>
                <div class="status-user">{{username}}账户</div>
            <div class="status__btn" @click="useCoupon()">立即使用</div>
        </div>
         <div class="receive-status" v-if="(receiveFail && !loginMethods) || !couponSwitch">
            <img src="static/images/coupon-no.png" class="status-icon"/>
            <div class="status-text">{{failmessage}}</div>
        </div>
        <fast-login v-if="!login && canuse && couponSwitch" @lReceiveCoupon="lReceiveCoupon"></fast-login>
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
            currentUserCoupon:null, //领取后的优惠券信息
            login:false,//登录状态
            canuse:false, //当前优惠券失效了
            hasReceive:false, //是否已经领取了优惠券
            receiveFail:false, //优惠券是否能正常使用
            failmessage: '优惠券已失效',//失败提示
            successmessage:'',//成功提示
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
            settings: state => state.settings,
            couponSwitch: state => state.couponSwitch
        }),
        username:{
            get:function(){
                //未登录没有用户名
                if(!this.$store.state.token){
                    return;
                }
                //是否绑定了手机，有手机号，手机号优先
                if(this.user.verifiedMobile){
                    return this.user.verifiedMobile
                }else{
                    return this.user.nickname
                }

            }
        }
    },
    filters: {
        couponType(type){
            return type=='discount' ? '折':'元'
        }
    },
    methods:{
        //通过链接获取优惠券信息
        async getCouponInfo(){
            const token = this.$route.params.token;
            await Api.getCouponInfo({
               query: {
                    token: token
                }
            }).then((res)=>{
                this.coupons=res;
                //判断优惠券是否还能用
                let cantUseCoupon= !this.cantUseCoupon(res);
                if(cantUseCoupon){
                   this.isLogin();
                }
            }).catch((err)=>{

                Toast.fail(err.message);
            })
        },
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
        //优惠券类型过滤
        couponType(coupons) {
            //指定优惠
            const numberType = coupons.targetDetail.numType;
            const productType = coupons.targetDetail.product;
            if (numberType === 'single') {
                switch (productType) {
                    case 'course':
                    case 'classroom':
                        return '指定商品'
                        break;
                    case 'vip':
                        return '指定会员'
                        break;
                    default:
                        return ''
                }
            } else if (numberType === 'all') {
                //全部
                switch (productType) {
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
            } else {
                switch (productType) {
                    case 'course':
                    case 'classroom':
                        return '部分商品'
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
                    this.currentUserCoupon=res;
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
            if(this.currentUserCoupon!=null && this.currentUserCoupon.deadline){ //已经过期
                if(this.isOld(this.currentUserCoupon.deadline)){
                    Toast.fail('优惠券已过期');
                    return ;
                }
            }
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
        //判断优惠券是否过期
        isOld(time){
            let ONEDAY=86400000;
            let d1=new Date();
            let d2 = new Date(Date.parse(time));
            if(d1.getTime()>(d2.getTime()+ONEDAY)){
                return true;
            }
            return false;
        },
        //判断优惠券是否可用
        cantUseCoupon(coupon){
            if(coupon.currentUserCoupon!=null){ //已经领取过
                this.currentUserCoupon=coupon.currentUserCoupon;
                this.successmessage="您已领取过该批次优惠券，优惠券已放入";
                this.hasReceive=true;
                this.canuse=false;
                return true;
            }
            if(Number(coupon.unreceivedNum)==0){ //已领完
                this.failmessage="优惠券已领完"
                this.receiveFail=true;
                this.canuse=false;
                return true;
            }
           if(coupon.deadline){ //已经过期
                if(this.isOld(coupon.deadline)){
                    this.failmessage="优惠券已过期"
                    this.receiveFail=true;
                    this.canuse=false;
                    return true;
                }
            }
            this.canuse=true;
            return false
        }
    }
}
</script>

