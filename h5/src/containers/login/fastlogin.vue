<template>
    <div class="login">
        <span class="login-title">手机快捷登录</span>
        <img class="login-avatarimg" src="" />
        <van-field
            v-model="userinfo.mobile"
            placeholder="请输入手机号"
            maxLength="11"
            class="login-input e-input"
            border
            clearable
            :error-message="errorMessage.mobile"
            @blur="validateMobileOrPsw('mobile')"
            @keyup="validatedChecker()"
        />

        <e-drag
            ref="dragComponent"
            v-if="dragEnable"
            limitType="sms_login"
            :key="dragKey"
            @success="handleSmsSuccess"></e-drag>

        <van-field
            v-model="userinfo.smsCode"
            type="text"
            center
            border
            clearable
            maxLength="6"
            class="login-input e-input"
            placeholder="请输入验证码"
            >
            <van-button
            slot="button"
            size="small"
            type="primary"
            :disabled="count.codeBtnDisable || !validated.mobile"
            @click="clickSmsBtn">
            发送验证码
            <span v-show="count.showCount">({{ count.num }})</span>
            </van-button>
        </van-field>
        <van-button type="default" class="primary-btn mb20" @click="handleSubmit" :disabled="btnDisable">登录</van-button>
        <div class="login-bottom text-center">
            <div class="login-agree">
                <van-checkbox v-model="agreement" @click="checkAgree" checked-color="#408ffb" :icon-size="16">
                    我已阅读并同意《<i @click="lookPrivacyPolicy">用户服务协议</i>》
                 </van-checkbox>
                 <div class="agree-text">新用户将为您自动注册</div>
            </div>
            <div class="login-change" @click="changeLogin">切换账号密码登录</div>
        </div>  
        <!-- <div class="login-footer"> </div> -->
    </div>
</template>
<script>
import EDrag from '@/containers/components/e-drag';
import rulesConfig from '@/utils/rule-config.js';
import XXTEA from '@/utils/xxtea.js';
import { Toast } from 'vant';
import Api from '@/api';
import activityMixin from '@/mixins/activity';
import redirectMixin from '@/mixins/saveRedirect';
import { mapActions, mapState } from 'vuex';
export default {
    name:'fast-login',
    mixins: [activityMixin, redirectMixin],
    components: {
        EDrag
    },
    data(){
        return{
            userinfo: {
                mobile: '',
                dragCaptchaToken: undefined, // 默认不需要滑动验证,图片验证码token
                smsCode: '',//验证码
                smsToken: '',//验证码token
                type:'sms_login',
            },
            registerSettings:null,
            agreement:true,
            dragEnable: false,
            dragKey: 0,
            errorMessage: {
                mobile: '',
            },
            validated: {
                mobile: false,
            },
            count: {
                showCount: false,
                num: 60,
                codeBtnDisable: false
            },
      }
    },
    computed:{
        btnDisable() {
            return !(this.userinfo.mobile
            && this.userinfo.smsCode
            && this.agreement);
        },
    },
    async created(){
        if (this.$store.state.token) {
            Toast.loading({
                message: '请稍后'
            });
            this.afterLogin();
            return;
         }
    },
    methods:{
        ...mapActions([
            'addUser',
            'setMobile',
            'sendSmsCenter',
            'fastLogin'
        ]),
        //获取隐私政策
         lookPrivacyPolicy(){
           window.location.href = '/mapi_v2/School/getPrivacyPolicy'
        },
        //校验手机号
        validateMobileOrPsw(type = 'mobile') {
            const ele = this.userinfo[type];
            const rule = rulesConfig[type];

            if (ele.length == 0) {
                this.errorMessage[type] = '';
                return false;
            };

            this.errorMessage[type] = !rule.validator(ele)
                ? rule.message: '';
        },
        validatedChecker() {
            const mobile = this.userinfo.mobile;
            const rule = rulesConfig['mobile'];

            this.validated.mobile = rule.validator(mobile);
        },
        //校验成功
        handleSmsSuccess(token) {
            this.userinfo.dragCaptchaToken = token;
            this.handleSendSms();
        },
        //登录
        handleSubmit() {
            this.fastLogin({
                mobile: this.userinfo.mobile,
                smsToken: this.userinfo.smsToken,
                smsCode: this.userinfo.smsCode,
                loginType: 'sms',
            }).then((res) =>{
                this.afterLogin();
            }).catch((err) =>{
                Toast.fail(err.message);
            })
        },
        clickSmsBtn() {
            if (!this.dragEnable) {
                this.handleSendSms();
                return;
            }
            // 验证码组件更新数据
            if (!this.$refs.dragComponent.dragToEnd) {
                Toast('请先完成拼图验证');
                return;
            }
            this.$refs.dragComponent.initDragCaptcha();
            },
        handleSendSms() {
            this.sendSmsCenter(this.userinfo)
            .then( (res) => {
                this.userinfo.smsToken = res.smsToken;
                this.countDown();
                this.dragEnable=false;
            })
            .catch(err => {
                switch(err.code) {
                case 4030301:
                case 4030302:
                    this.dragKey ++;
                    this.userinfo.dragCaptchaToken = '';
                    this.userinfo.smsToken = '';
                    Toast.fail(err.message);
                    break;
                case 4030303:
                    if (this.dragEnable) {
                    Toast.fail(err.message);
                    } else {
                    this.dragEnable = true;
                    }
                    break;
                default:
                    Toast.fail(err.message);
                    break;
                }
            });
        },
        // 倒计时
        countDown() {
            this.count.showCount = true;
            this.count.codeBtnDisable = true;
            this.count.num = 120;

            const timer = setInterval(() => {
                if(this.count.num <= 0) {
                this.count.codeBtnDisable = false;
                this.count.showCount = false
                clearInterval(timer);
                return;
                }
                this.count.num--;
            }, 1000);
        },
        //同意协议
        checkAgree(){
            this.agreement=!this.agreement
        },
        changeLogin(){
            this.$router.push({
                name: 'login',
            })
        }
    }
}
</script>

