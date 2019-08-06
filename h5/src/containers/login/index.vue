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
      <div class="login-change" v-show="cloudSetting" @click="changeLogin">
          <img src="static/images/login_change.png" class="login_change-icon"/>切换手机快捷登录
      </div>
    </div>  
  </div>

</template>
<script>
import activityMixin from '@/mixins/activity';
import redirectMixin from '@/mixins/saveRedirect';
import { mapActions } from 'vuex';
import Api from '@/api';
import { Toast } from 'vant';
//import passlogin from '@/mixins/login/passlogin';

export default {
  mixins: [redirectMixin],
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
      cloudSetting:false
    }
  },
  async created () {
    if (this.$store.state.token) {
      Toast.loading({
        message: '请稍后'
      });
      this.afterLogin();
      return;
    }
    this.registerSettings = await Api.getSettings({
      query: {
        type: 'register'
      }
    }).catch(err => {
      Toast.fail(err.message)
    });
    this.getsettingsCloud();
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
    //网校是否开启短信云服务
    async getsettingsCloud(){
      await Api.settingsCloud().then(res=>{
        if(res.sms_enabled){
          this.cloudSetting=true;
        }else{
          this.cloudSetting=false;
        }
      }).catch(err => {
        Toast.fail(err.message)
      });
    },
    onSubmit(data) {
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
          redirect: this.$route.query.redirect || '/',
          callbackType: this.$route.query.callbackType,
          activityId: this.$route.query.activityId,
        }
      });
    },
    changeLogin(){
      /* 需要到登录权限的页面／组件，跳转前把当前路由记录下来，便于登陆后回到页面 */
      if(this.$route.query.redirect){
        this.$router.push({
          name: 'fastlogin',
          query: {
              redirect: this.$route.query.redirect
          }
        })
      }else{
        this.$router.push({
          name: 'fastlogin',
        })
      }
      
    }
  },

  mounted() {
    this.bodyHeight = document.documentElement.clientHeight - 46;
    this.username = this.$route.params.username || this.$route.query.account || '';
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
