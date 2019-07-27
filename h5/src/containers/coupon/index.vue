<template>
    <div class="coupon-receive">
        <div class="coupon-receive-title text-overflow">
            第一网校 / 杭州电子科技大学建筑...
        </div>
        <div class="coupon-receive-ticket">
            <div class='ticket-discount'>
                <span class="rate">7.5</span>折
                <span>优惠券</span>
            </div>
            <div class='ticket-expire text-overflow'>有效期至：2019年7月19日</div>
            <div class='ticket-range text-overflow'>适用于：所有课程 / 网校如何引流转化的...</div>
        </div>
        <div class="receive-status" v-if="login">
            <img src="static/images/coupon-yes.png" class="status-icon"/>
            <div class="status-text">领取成功，优惠券已放入</div>
            <div class="status-user">cccc账户</div>
            <div class="status__btn">立即使用</div>
        </div>
        <pass-login v-if="login"></pass-login>
         <fast-login ></fast-login>
    </div>
</template>
<script>
import passLogin from './component/passlogin'
import fastLogin from './component/fastlogin'
export default {
    name:"getcoupon",
    components:{
        passLogin,
        fastLogin,
    },
    data(){
        return{
            login:false
        }
    }
}
</script>

