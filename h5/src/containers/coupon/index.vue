<template>
    <div class="coupon-receive" v-if="coupons">
        <e-loading v-if="isLoading"></e-loading>
        <div class="coupon-receive-title text-overflow">
            {{coupons.name}}
        </div>
        <div class="coupon-receive-ticket">
            <div class='ticket-discount'>
                <span class="rate">{{parseFloat(coupons.rate)}}</span>{{ coupons.type| couponType}}
                <span>优惠券</span>
            </div>
            <div class='ticket-expire text-overflow'>
                 <span v-if="coupons.deadlineMode==='day'">领取后{{coupons.fixedDay}}天内有效</span>
                <span v-if="coupons.deadlineMode==='time'">{{ timeExpire(coupons.createdTime, coupons.deadline) }}</span>
            </div>
            <div class='ticket-range text-overflow'>适用于：{{couponType(coupons)}}</div>
        </div>
        <div class="receive-status" v-if="login && hasReceive && !loginMethods">
            <img src="static/images/coupon-yes.png" class="status-icon"/>
            <div class="status-text">领取成功，优惠券已放入</div>
            <div class="status-user">{{user.nickname}}账户</div>
            <div class="status__btn" @click="useCoupon()">立即使用</div>
        </div>
         <div class="receive-status" v-if="ReceiveFail">
            <img src="static/images/coupon-no.png" class="status-icon"/>
            <div class="status-text">{{failmessage}}</div>
        </div>
        <fast-login v-if="!login && cloudSetting" @lReceiveCoupon="lReceiveCoupon"></fast-login>
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
    name:"getcoupon",
    components:{
        fastLogin
    },
    mixins: [couponMixin, getCouponMixin],
    data(){
        return{
            coupons:null,//优惠券信息
            login:false,//登录状态
            cloudSetting:false,//云短信
            hasReceive:false,
            ReceiveFail:false, //优惠券是否能正常使用
            failmessage:'',
            loginMethods:false
        }
    },
    created(){
        this.getCouponInfo();
    },
    computed: {
    ...mapState({
        user: state => state.user,
        isLoading: state => state.isLoading
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
                //判断优惠券是否已经过期
               let canUseCoupon= this.canUseCoupon(res);
               if(canUseCoupon){
                   this.isLogin();
               }
            }).catch((err)=>{
                Toast.fail(err.message);
            })
        },
        //是否开启云短信
        async getsettingsCloud(){
            await Api.settingsCloud().then(res=>{
                //开启了云短信
                if(res.sms_enabled==1){
                    this.cloudSetting=true;
                }else{
                    this.cloudSetting=false;
                    //跳转登录页
                    this.toLogin();
                }
            }).catch(err => {
                Toast.fail(err.message)
            });
        },
        //登录后逻辑
        isLogin(){
             if (this.$store.state.token) {
                this.login=true;
                let data={
                    batchToken:this.$route.params.token
                }
                this.newReceiveCoupon(data);
                return;
            }
            this.getsettingsCloud();
        },
        //跳转到登录页
        toLogin(){
            this.$router.push({
                name: 'login',
                query: {
                    redirect: this.$route.fullPath,
                }
            });
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
               if(res.code=="success"){
                   this.hasReceive=true;
                   if(loginReceive){
                       Toast.success('领取成功，正在跳转到详情页...')
                       setTimeout(()=>{
                           this.useCoupon();
                       },3000)  
                   }
               }else if(res.code=="failed"){
                   this.ReceiveFail=true;
                   this.failmessage='领取失败，'+res.message;
               }
            }).catch((err) => {
                Toast.fail(err.message)
            });
        },
        //领取优惠券后跳转指定页面
        useCoupon(){
            this.hasreceiveCoupon(this.coupons);
        },
        //登录后领取优惠券
        lReceiveCoupon(user,loginReceive){
            let data={
                batchToken:this.$route.params.token,
                ...user
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
                    this.ReceiveFail=true;
                    result=false
                }
            }else if(coupon.unreceivedNum==0){
                this.failmessage="优惠券已领完"
                this.ReceiveFail=true;
                result=false
            }
            return result
        }
    }
}
</script>

