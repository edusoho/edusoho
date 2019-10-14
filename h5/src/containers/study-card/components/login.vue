<template>
  <div class="login">
    <van-action-sheet
        v-model="visible"
        title=" "
        @close="updateShow(show)"
    >
      <div class="login__container">
        <div class="receive-login-input">
          <van-field
              v-model="userinfo.mobile"
              type="text"
              :placeholder="currentLoginMode.accountPlaceholder"
              clearable
              :border=false
              class="login__container__field"
              :error-message="errorMessage.mobile"
              @blur="loginMode === 'fastLoginMode' && validateMobileOrPsw('mobile')"
              @input="loginMode === 'fastLoginMode' && validatedChecker()"
          >
          </van-field>
        </div>

        <div class="receive-login-input">
          <van-field
              v-model="userinfo.smsCode"
              type="text"
              :border=false
              clearable
              maxlength=6
              ref="smsCode"
              :placeholder="currentLoginMode.passwordPlaceholder"
              class="login__container__field"
          >
            <div
                v-show="loginMode === 'fastLoginMode'"
                slot="button"
                @click="clickSmsBtn" :class="['code-btn',cansentCode ? '': 'code-btn--disabled']"
            >
              <span v-show="!count.showCount">发送验证码</span>
              <span v-show="count.showCount">{{ count.num }} s</span>
            </div>
          </van-field>
          <router-link
              to="/setting/password/reset"
              class="reset-password"
              tag="div"
          >忘记密码？
          </router-link>
        </div>
        <div class="mobile-drag" v-if="dragEnable">
          <div class="mobile-drag-content">
            <e-drag
                v-if="dragEnable"
                ref="dragComponent"
                limitType="receive_coupon"
                :key="dragKey"
                @success="handleSmsSuccess"></e-drag>
          </div>
        </div>
        <div :class="['receive-login__btn',btnDisable ? 'disabled__btn' : '']" @click="handleSubmit(handleSubmitSuccess)">登录并领取</div>
        <div class="choice-bar">
          <router-link to="/register" tag="div" class="left">注册账号</router-link>
          <div class="right" v-show="loginMode === 'fastLoginMode'" @click="changeLoginMode">使用其他方式登录 >></div>
          <div class="right" v-show="loginMode === 'normalLoginMode'" @click="changeLoginMode">使用手机快捷登录 >></div>
        </div>
        <div class="receive-login__text">
          <span class="receive-login-tools" v-show="loginMode === 'fastLoginMode'">新用户领取将为您自动注册</span>
          <div
              v-show="loginMode === 'normalLoginMode'"
              class="third-part-login"
          >
            <span class="third-part-login-tools">更多登录方式</span>
          </div>
        </div>
      </div>
    </van-action-sheet>
  </div>
</template>

<script>
  import EDrag from '@/containers/components/e-drag';
  import fastLoginMixin from '@/mixins/fastLogin';
  import { mapActions } from 'vuex';
  import activityMixin from '@/mixins/activity';
  import redirectMixin from '@/mixins/saveRedirect';

  export default {
    name: 'login',
    components: {
      EDrag
    },
    props: {
      show: {
        type: Boolean,
        default: false
      },
      isLogin: {
        type: Boolean,
        default: false
      },
      processIsDone: {
        type: Boolean,
        default: false
      }
    },

    data() {
      return {
        visible: this.show,
        userinfo: {
          mobile: '',
          dragCaptchaToken: undefined, // 默认不需要滑动验证,图片验证码token
          smsCode: '',//验证码
          smsToken: '',//验证码token
          type: 'sms_login'
        },
        dragEnable: false,
        dragKey: 0,
        errorMessage: {
          mobile: '',
        },
        validated: {
          mobile: false,
        },

        // fastLoginMode 为手机快捷登录，normalLoginMode 为账号密码登录
        loginMode: 'fastLoginMode',
        currentLoginMode: {},
        fastLoginMode: {
          accountPlaceholder: '请输入手机号',
          passwordPlaceholder: '请输入验证码',
        },
        normalLoginMode: {
          accountPlaceholder: '请输入手机号/邮箱号/用户名',
          passwordPlaceholder: '请输入密码',
        }
      };
    },
    computed: {
      btnDisable() {
        return !(this.userinfo.mobile && this.userinfo.smsCode);
      },
      cansentCode() {
        return !(this.count.codeBtnDisable || !this.validated.mobile);
      }
    },
    watch: {
      show() {
        this.visible = this.show;
      }
    },
    mixins: [activityMixin, redirectMixin, fastLoginMixin],
    created() {
      this.currentLoginMode = this[this.loginMode];
    },
    methods: {
      ...mapActions([
        'addUser',
        'setMobile',
        'sendSmsSend',
        'fastLogin'
      ]),
      updateShow() {
        this.$emit('update:show', false);
      },
      updateIsLogin() {
        this.$emit('update:isLogin', true);
      },
      updateProcessIsDone() {
        this.$emit('update:processIsDone', true);
      },

      // 校验成功
      handleSmsSuccess(token) {
        this.userinfo.dragCaptchaToken = token;
        this.handleSendSms();
      },
      handleSubmitSuccess() {
        let data = {
          mobile: this.userinfo.mobile,
          smsToken: this.userinfo.smsToken,
          smsCode: this.userinfo.smsCode,
        };
        this.$emit('lReceiveCoupon', data);
        this.updateShow();
        this.updateIsLogin();
        this.updateProcessIsDone();
      },
      changeLoginMode() {
        this.loginMode = this.loginMode === 'fastLoginMode' ? 'normalLoginMode' : 'fastLoginMode';
        this.currentLoginMode = this[this.loginMode];
        this.userinfo.mobile = '';
        this.userinfo.smsCode = '';
      }
    },
  };
</script>

<style scoped>

</style>
