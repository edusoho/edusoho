<template>
  <div class="register">
    <span class='register-title'>注册账号</span>

      <van-field
        v-model="registerInfo.mobile"
        placeholder="请输入手机号"
        maxLength="11"
      />

      <van-field
        v-model="registerInfo.encrypt_password"
        type="password"
        placeholder="请设置密码（5-20位字符）"
      />
      <van-field
        v-model="registerInfo.code"
        type="text"
        center
        clearable
        placeholder="请输入验证码"
        >
      <van-button slot="button" size="small" type="primary">发送验证码</van-button>
      </van-field>
      
      <!-- <span class='register-hint'>验证码已发送到：{{ phone }}</span> -->
      <e-drag :info="registerInfo" @success="handleSmsSuccess"></e-drag>
      <van-button type="default" 
        class="primary-btn mb20" 
        :disabled="btnDisable"
        @click="handleSubmit">同意服务协议并注册</van-button>
      
      <div class="login-bottom ">
        请详细阅读 <router-link to="/protocol">《用户服务协议》</router-link> 
      </div>
      <!-- 一期不做 -->
      <!-- <div class="register-social">
        <span>
          <i class="iconfont icon-qq"></i>
          <i class="iconfont icon-weixin1"></i>
          <i class="iconfont icon-weibo"></i>
        </span>
        <div class="line"></div>
      </div> -->
  </div>
</template>
<script>
import EDrag from '@/containers/components/e-drag';
import { mapActions } from 'vuex';
import XXTEA from '@/utils/xxtea.js';
import { Toast } from 'vant';

export default {
  components: {
    EDrag
  },
  data() {
    return {
      registerInfo: {
        mobile: '',
        type: 'register',
        dragCaptchaToken: '',
        encrypt_password: '',
        code: '',
        smsToken: ''
      },
      options: [{
        model: 'email'
      }, {
        model: 'mobile'
      }]
    }
  },
  computed: {
    btnDisable() {
      return !(this.registerInfo.mobile && this.registerInfo.encrypt_password);
    }
  },
  methods: {
    ...mapActions([
      'addUser'
    ]),
    handleSmsSuccess(data) {
      this.registerInfo.smsToken = data.smsToken;
    },
    handleSubmit() {
      const password = this.registerInfo.encrypt_password;

      this.registerInfo.encrypt_password =
        window.XXTEA.encryptToBase64(password, window.location.host);

      this.addUser(this.registerInfo)
      .then()
      .catch(err => {
        Toast.fail(err.message);
      });
    }
  }
}
</script>

