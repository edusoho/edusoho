<template>
  <div class="my_setting-nickname">
    <van-field v-model="nickname" placeholder="请修改您的用户名" class=" my_setting-nickname--input"/>
    <van-button type="default" 
    @click="setNickname"
    :disabled="btnDisable"
    class="primary-btn my_setting-nickname—-btn">确定</van-button>
  </div>
</template>
<script>
import Api from '@/api';

export default {
  data () {
    return {
      nickname: '',
    }
  },
  computed: {
    btnDisable() {
      return this.nickname.length <= 3;
    }
  },
  methods: {
    setNickname() {
      Api.setNickname({
        nickname: this.nickname
      }).then(res => {
        console.log(res);
      })
    }
  }
}
</script>
