<template>
    <div class="coupon-receive-login">
        <div class="receive-login-input">
            <van-field 
                v-model="username" 
                type="number"
                placeholder="请输入手机号码" 
                error-message=""
                clearable/>
        </div>
        <div class="receive-login-input">
            <van-field 
                v-model="password" 
                type="number"
                placeholder="请输入验证码"
                clearable
                maxLength="6"
                error-message="">
                <div slot="button" :class="['code-btn',cansentCode ? '': 'code-btn--disabled']">发送验证码</div>
            </van-field>
        </div>
        <div :class="['receive-login__btn',btnDisable ? 'disabled__btn' : '']" >立即领取</div>
    </div>
</template>
<script>
export default {
    name:'coupon-fast',
    data(){
        return {
            username: '',
            password: '',
            errorMessage: {
                password: ''
            },
            cansentCode:false,
            faceSetting: 0,
            bodyHeight: 520,
            loginConfig: {},
        }
    },
    computed: {
        btnDisable() {
            return !(this.username && this.password);
        }
    }
}
</script>

