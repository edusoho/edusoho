<template>
    <div class="coupon-receive-login">
        <div class="receive-login-input">
            <van-field 
            v-model="username" 
            placeholder="邮箱/手机/用户名" 
            error-message=""
            clearable/>
        </div>
        <div class="receive-login-input">
            <van-field 
            v-model="password" 
            placeholder="请输入密码"
            error-message="" />
        </div>
        <div :class="['receive-login__btn',btnDisable ? 'disabled__btn' : '']" >立即领取</div>
    </div>
</template>
<script>
export default {
    name:'coupon-pass',
    data(){
        return{
            username: '',
            password: '',
            errorMessage: {
                password: ''
            },
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