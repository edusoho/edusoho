<template>
  <div class="register">
    <span class='register-title'>创建账号</span>

    <template v-if="step == '0'">
      <img class='register-avatarimg' src="" />
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
        placeholder="请输入密码，5-20位英文、数字"
      />
     <van-button type="default" class="primary-btn" @click="step = 1">下一步</van-button>
    </template>
    
    <template v-if="step == '1'">
      <span class='register-hint'>验证码已发送到：{{ phone }}</span>
      <img class='register-avatarimg' src="" />
      
      <van-button type="default" class="primary-btn">确定</van-button>
    </template>
  </div>
</template>
<script>
export default {
  data() {
    return {
      phone: '',
      username: '',
      password: '',
      step: 0
    }
  }
}
</script>

