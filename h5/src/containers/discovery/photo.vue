<template>
  <div class="login">
    <span class='login-title'>请将面部正对摄像头</span>
    <van-button type="default" class="primary-btn mb20" @click="onSubmitInfo">下一步</van-button>
  </div>
</template>


<script>
  import { mapState } from 'vuex';
  import Api from '@/api'

export default {
  computed: {
    ...mapState({
      user: state => state.user
    }),
  },

  methods: {
    onSubmitInfo() {
      console.log(this.user);
    }
  }

}
</script>
