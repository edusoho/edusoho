<template>
  <div class="register">
    <span class='register-title'>注册账号</span>

      <van-field
        v-model="phone"
        placeholder="请输入手机号"
      />

      <van-field
        v-model="username"
        placeholder="请输入用户名，最长18个英文或9个汉字"
      />

      <van-field
        v-model="password"
        type="password"
        placeholder="请设置密码（5-20位字符）"
      />

      <van-field
        v-model="password"
        type="password"
        placeholder="请填写您的邀请码（选填）"
      />
      
      <!-- <span class='register-hint'>验证码已发送到：{{ phone }}</span> -->
      
      <van-button type="default" class="primary-btn mb20">同意服务协议并注册</van-button>
      
      <div class="login-bottom ">
        请详细阅读 <router-link to="/register">《用户服务协议》</router-link> 
      </div>
      <div class="register-social">
        <span>
          <i class="iconfont icon-qq"></i>
          <i class="iconfont icon-weixin1"></i>
          <i class="iconfont icon-weibo"></i>
        </span>
        
        <div class="line"></div>
      </div>
  </div>
</template>
<script>
export default {
  data() {
    return {
      phone: '',
      username: '',
      password: '',
      options: [{
        model: 'email'
      }, {
        model: 'mobile'
      }]
    }
  }
}
</script>

