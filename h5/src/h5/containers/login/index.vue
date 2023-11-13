<template>
  <div :style="{ height: bodyHeight + 'px' }" class="login">
    <span class="login-title">{{ $t('title.loginAccount') }}</span>
    <img class="login-avatarimg" src="" />
    <van-field
      v-model="username"
      :autosize="{ maxHeight: 24 }"
      :border="false"
      type="textarea"
      class="login-input e-input"
      :placeholder="$t('placeholder.emailMobileNumberUserName')"
    />

    <van-field
      v-model="password"
      :border="false"
      :error-message="errorMessage.password"
      type="password"
      class="login-input e-input"
      :placeholder="$t('placeholder.password')"
    />

    <div class="login-register">
      <router-link to="/setting/password/reset" class="login-account">
        {{ $t('btn.forgetPassword') }} ？ &nbsp;|
      </router-link>
      <span class="login-account" @click="jumpRegister">
        &nbsp; {{ $t('btn.registerNow') }} &nbsp;
      </span>
    </div>
    <div class="w-full btn-login">
       <van-button
          :disabled="btnDisable"
          type="info"
          class="primary-btn mb20 w-full"
          @click="onSubmit"
          >{{ $t('btn.login') }}</van-button
        >
    </div>
     
   
    <div class="login-bottom text-center">
      <div v-if="userTerms || privacyPolicy" class="login-agree">
        <van-checkbox
          v-model="agreement"
          :icon-size="16"
          checked-color="#408ffb"
        />
        {{ $t('tips.iHaveReadAndAgreeToThe') }}
        <i v-if="userTerms" @click="lookUserTerms"
          >《{{ $t('btn.userServiceAgreement') }}》</i
        >
        <span v-if="userTerms && privacyPolicy">{{ $t('tips.and') }}</span>
        <span v-if="privacyPolicy">
          <i @click="lookPrivacyPolicy">《{{ $t('btn.privacyAgreemen') }}》</i>
        </span>
      </div>

      <div v-show="cloudSetting" class="login-change" @click="changeLogin">
        <img src="static/images/login_change.png" class="login_change-icon" />{{
          $t('btn.loginWithMobileNumber')
        }}
      </div>
    </div>

    <van-popup
      v-model="popUpBottom"
      class="login-pop"
      position="bottom"
      round
      :style="{ height: '30%' }"
    >
      <div class="login-pop-title">{{ $t('btn.PleaseReadAgreeAndTerms') }}</div>
      <div v-if="userTerms || privacyPolicy" class="login-agree">
        <i v-if="userTerms" @click="lookUserTerms"
          >《{{ $t('btn.userServiceAgreement') }}》</i
        >
        <span v-if="privacyPolicy">
          <i @click="lookPrivacyPolicy">《{{ $t('btn.privacyAgreemen') }}》</i>
        </span>
      </div>
        <van-button
        :disabled="btnDisable"
        type="info"
        class="primary-btn mb20 login-pop-btn"
        @click="agreeSign"
        >{{ $t('btn.agreeAndSignin') }}</van-button
      >
     
    </van-popup>
  </div>
</template>
<script>
import redirectMixin from '@/mixins/saveRedirect';
import { mapActions } from 'vuex';
import Api from '@/api';
import { Toast } from 'vant';
// import passlogin from '@/mixins/login/passlogin';

export default {
  mixins: [redirectMixin],
  data() {
    return {
      username: '',
      password: '',
      errorMessage: {
        password: '',
      },
      faceSetting: 0,
      bodyHeight: 520,
      loginConfig: {},
      cloudSetting: false,
      userTerms: false, // 用户协议
      privacyPolicy: false, // 隐私协议
      agreement: false, // 是否勾选
      popUpBottom: false, // 底部弹出层
    };
  },
  computed: {
    btnDisable() {
      return !(this.username && this.password);
    },
    isWeixinBrowser() {
      return /micromessenger/.test(navigator.userAgent.toLowerCase());
    },
    canloginConfig() {
      if (this.$route.query && this.$route.query.forbidWxLogin) {
        return false;
      }
      return true;
    },
  },
  async created() {
    if (this.$store.state.token) {
      Toast.loading({
        message: this.$t('toast.pleaseWait'),
      });
      this.afterLogin();
      return;
    }
    this.registerSettings = await Api.getSettings({
      query: {
        type: 'register',
      },
    }).catch(err => {
      Toast.fail(err.message);
    });
    this.getsettingsCloud();
    this.getPrivacySetting();
  },
  mounted() {
    this.bodyHeight = document.documentElement.clientHeight - 46;
    this.username =
      this.$route.params.username || this.$route.query.account || '';
    Toast.loading({
      message: this.$t('toast.pleaseWait'),
    });
    this.faceLogin();
    this.thirdPartyLogin();
  },
  methods: {
    ...mapActions(['userLogin']),
    async getPrivacySetting() {
      await Api.getSettings({
        query: {
          type: 'user',
        },
      })
        .then(res => {
          if (res.auth.user_terms_enabled) {
            this.userTerms = true;
          }
          if (res.auth.privacy_policy_enabled) {
            this.privacyPolicy = true;
          }
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    },
    // 隐私政策
    lookPrivacyPolicy() {
      window.location.href =
        window.location.origin + '/mapi_v2/School/getPrivacyPolicy';
    },
    // 获取服务条款
    lookUserTerms() {
      window.location.href =
        window.location.origin + '/mapi_v2/School/getUserterms';
    },
    // 网校是否开启短信云服务
    async getsettingsCloud() {
      await Api.settingsCloud()
        .then(res => {
          this.cloudSetting = !!res.sms_enabled;
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    },
    onSubmit(data) {
      if (
        this.agreement ||
        (this.privacyPolicy === false && this.userTerms === false)
      ) {
        this.userLogin({
          username: this.username,
          password: this.password,
        })
          .then(res => {
            Toast.success({
              duration: 1000,
              message: this.$t('toast.signInSuccessfully'),
            });
            this.afterLogin();
          })
          .catch(err => {
            Toast.fail(err.message);
          });

        return;
      }

      this.popUpBottom = true;
    },
    agreeSign() {
      this.userLogin({
        username: this.username,
        password: this.password,
      })
        .then(res => {
          this.agreement = true;
          this.popUpBottom = false;
          Toast.success({
            duration: 1000,
            message: this.$t('toast.signInSuccessfully'),
          });
          this.afterLogin();
        })
        .catch(err => {
          Toast.fail(err.message);
          this.popUpBottom = false;
        });
    },

    jumpRegister() {
      if (
        !this.registerSettings ||
        this.registerSettings.mode === 'closed' ||
        this.registerSettings.mode === 'email'
      ) {
        Toast(this.$t('toast.contactTheAdministrator'));
        return;
      }
      this.$router.push({
        name: 'register',
        query: {
          redirect: this.$route.query.redirect || '/',
        },
      });
    },
    faceLogin() {
      // 人脸登录配置
      Api.settingsFace({})
        .then(res => {
          if (Number(res.login.enabled)) {
            this.faceSetting = Number(res.login.h5_enabled);
          } else {
            this.faceSetting = 0;
          }
        })
        .catch(err => {
          Toast.fail(err.message);
        });
    },
    thirdPartyLogin() {
      if (!this.canloginConfig) {
        return;
      }
      // 第三方登录配置
      Api.loginConfig({})
        .then(res => {
          Toast.clear();
          this.loginConfig = res;
          if (Number(res.weixinmob_enabled) && this.isWeixinBrowser) {
            this.wxLogin();
          }
        })
        .catch(err => {
          Toast.fail(err.message);
          Toast.clear();
        });
    },
    wxLogin() {
      this.$router.replace({
        path: '/auth/social',
        query: {
          type: 'wx',
          weixinmob_key: this.loginConfig.weixinmob_key,
          redirect: this.$route.query.redirect || '/',
          callbackType: this.$route.query.callbackType,
          activityId: this.$route.query.activityId,
        },
      });
    },
    changeLogin() {
      /* 需要到登录权限的页面／组件，跳转前把当前路由记录下来，便于登陆后回到页面 */
      if (this.$route.query.redirect) {
        this.$router.push({
          name: 'fastlogin',
          query: {
            redirect: this.$route.query.redirect,
          },
        });
      } else {
        this.$router.push({
          name: 'fastlogin',
        });
      }
    },
  },
};
</script>
<style lang="scss" scoped>
/* 小于 600px 的分辨率 */
@media (max-width: 600px) {
  .van-button {
    height: 44px !important;
  }
}

/* 大于等于 600px 的分辨率 */
@media (min-width: 600px) {
  .van-button {
    height: 4rem !important;
  }
}
</style>
