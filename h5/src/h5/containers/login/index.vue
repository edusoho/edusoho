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

    <van-button
      :disabled="btnDisable"
      type="info"
      class="primary-btn mb20"
      @click="onSubmit"
      >{{ $t('btn.login') }}</van-button
    >
    <div class="login-bottom text-center">
      <router-link to="/setting/password/reset" class="login-account">
        {{ $t('btn.forgetPassword') }} ？ &nbsp;|
      </router-link>
      <span class="login-account" @click="jumpRegister">
        &nbsp; {{ $t('btn.registerNow') }} &nbsp;
      </span>
      <div v-show="cloudSetting" class="login-change" @click="changeLogin">
        <img
          src="static/images/login_change.png"
          class="login_change-icon"
        />{{ $t('btn.loginWithMobileNumber') }}
      </div>
    </div>
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
        message: this.$t('toast.pleaseWait')
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
  },
  mounted() {
    this.bodyHeight = document.documentElement.clientHeight - 46;
    this.username =
      this.$route.params.username || this.$route.query.account || '';
    Toast.loading({
      message: this.$t('toast.pleaseWait')
    });
    this.faceLogin();
    this.thirdPartyLogin();
  },
  methods: {
    ...mapActions(['userLogin']),
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
