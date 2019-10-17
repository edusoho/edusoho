<template>
  <div class="coupon-receive-login">
    <div class="receive-login-input">
      <van-field
          v-model="userinfo.mobile"
          type="number"
          placeholder="请输入手机号码"
          clearable
          :border=false
          :error-message="errorMessage.mobile"
          @blur="validateMobileOrPsw('mobile')"
          @keyup="validatedChecker()"/>
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
    <div class="receive-login-input">
      <van-field
          v-model="userinfo.smsCode"
          type="number"
          placeholder="请输入验证码"
          clearable
          :border=false
          maxlength="6"
          ref="smsCode">
        <div slot="button" @click="clickSmsBtn" :class="['code-btn',cansentCode ? '': 'code-btn--disabled']">
          <span v-show="!count.showCount">发送验证码</span>
          <span v-show="count.showCount">{{ count.num }} s</span>
        </div>
      </van-field>
    </div>
    <div :class="['receive-login__btn',btnDisable ? 'disabled__btn' : '']" @click="handleSubmit(handleSubmitSuccess)">
      立即领取
    </div>
    <div class="receive-login__text">
      <span class="receive-login-tools">新用户领取将为您自动注册</span>
    </div>
  </div>
</template>
<script>
  import EDrag from '@/containers/components/e-drag';
  import rulesConfig from '@/utils/rule-config.js';
  import XXTEA from '@/utils/xxtea.js';
  import Api from '@/api';
  import activityMixin from '@/mixins/activity';
  import redirectMixin from '@/mixins/saveRedirect';
  import fastLoginMixin from '@/mixins/fastLogin';
  import { mapActions, mapState } from 'vuex';

  export default {
    name: 'coupon-fast',
    mixins: [activityMixin, redirectMixin, fastLoginMixin],
    components: {
      EDrag
    },
    data() {
      return {
        isShow: false,
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
        count: {
          showCount: false,
          num: 60,
          codeBtnDisable: false
        },
      };
    },
    computed: {
      btnDisable() {
        return !(this.userinfo.mobile
          && this.userinfo.smsCode);
      },
      cansentCode() {
        return !(this.count.codeBtnDisable || !this.validated.mobile);
      }
    },
    methods: {
      ...mapActions([
        'addUser',
        'setMobile',
        'sendSmsSend',
        'fastLogin'
      ]),
      handleSubmitSuccess() {
        const data = {
          mobile: this.userinfo.mobile,
          smsToken: this.userinfo.smsToken,
          smsCode: this.userinfo.smsCode,
        };
        this.$emit('lReceiveCoupon', data);
      },
      // 校验成功
      handleSmsSuccess(token) {
        this.userinfo.dragCaptchaToken = token;
        this.handleSendSms();
      },
    }
  };
</script>

