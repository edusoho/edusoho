<template>
  <div class="login" :style="{ height: bodyHeight + 'px'}">
    <span class="login-title">登录账号</span>
    <img class="login-avatarimg" src="" />
    <van-field v-model="username"
      :autosize="{ maxHeight: 24 }"
      type="textarea"
      class="login-input e-input"
      placeholder="邮箱/手机/用户名"/>

    <van-field v-model="password"
      type="password"
      class="login-input e-input"
      :error-message="errorMessage.password"
      placeholder="请输入密码" />
    <van-button type="default" class="primary-btn mb20" @click="onSubmit" :disabled="btnDisable">登录</van-button>
    <div class="login-bottom text-center">
      <router-link to="/setting/password/reset" class="login-account">忘记密码？ &nbsp;|</router-link>
      <span class="login-account" @click="jumpRegister">&nbsp; 立即注册 &nbsp;</span>
    </div>
    <div class="social-login">
      <router-link :to="{path: 'sts', query: {redirect: this.$route.query.redirect}}" class="social-login-button" v-if="faceSetting">
        <img src="static/images/face.png" alt="人脸识别登录图标">
      </router-link>
      <!-- 微信环境内，自动登录微信账号 -->
      <!-- <a class="social-login-button" v-if="Number(loginConfig.weixinmob_enabled) && isWeixinBrowser" @click="wxLogin">
        <i class="h5-icon h5-icon-weixin1"></i>
      </a> -->
    </div>
  </div>

</template>
<script>
import activityMixin from '@/mixins/activity';
import redirectMixin from '@/mixins/saveRedirect';
import { mapActions } from 'vuex';
import { Toast } from 'vant';
import Api from '@/api';

export default {
  mixins: [activityMixin, redirectMixin],
  data() {
    return {
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
  async created () {
    this.registerSettings = await Api.getSettings({
      query: {
        type: 'register'
      }
    }).catch(err => {
      Toast.fail(err.message)
    });
  },
  computed: {
    btnDisable() {
      return !(this.username && this.password);
    },
    isWeixinBrowser (){
      return /micromessenger/.test(navigator.userAgent.toLowerCase())
    },
  },
  methods: {
    ...mapActions([
      'userLogin'
    ]),
    onSubmit() {
      this.userLogin({
        username: this.username,
        password: this.password
      }).then(res => {
        Toast.success({
          duration: 2000,
          message: '登录成功'
        });
        this.afterLogin();
      }).catch(err => {
        Toast.fail(err.message);
      })
    },
    jumpRegister() {
      if (!this.registerSettings
        || this.registerSettings.mode == 'closed'
        || this.registerSettings.mode == 'email') {
        Toast('网校未开启手机注册，请联系管理员');
        return;
      }
      this.$router.push({
        name: 'register',
        query: {
          redirect: this.$route.query.redirect || '/'
        }
      })
    },
    wxLogin() {
      this.$router.replace({
        path: '/auth/social',
        query: {
          type: 'wx',
          weixinmob_key: this.loginConfig.weixinmob_key,
          redirect: this.$route.query.redirect || '/'
        }
      });
    }
  },

  mounted() {
    this.bodyHeight = document.documentElement.clientHeight - 46;
    this.username = this.$route.params.username || '';
    Toast.loading({
      message: '请稍后'
    });
    // 人脸登录配置
    Api.settingsFace({}).then(res => {
      if (Number(res.login.enabled)) {
        this.faceSetting = Number(res.login.h5_enabled);
      } else {
        this.faceSetting = 0;
      }
    }).catch(err => {
      Toast.fail(err.message)
    });

    // 第三方登录配置
    Api.loginConfig({}).then(res => {
      Toast.clear()
      this.loginConfig = res;
      if (Number(res.weixinmob_enabled) && this.isWeixinBrowser) {
        this.wxLogin();
      }
    }).catch(err => {
      Toast.fail(err.message)
      Toast.clear()
    });
  },
}
</script>
