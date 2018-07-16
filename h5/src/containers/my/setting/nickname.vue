<template>
  <div class="my_setting-nickname">
    <e-loading v-if="isLoading"></e-loading>
    <van-field v-model="nickname" placeholder="请修改您的用户名" class=" my_setting-nickname--input"/>
    
    <van-button type="default" 
    @click="modifyNickname"
    :disabled="btnDisable"
    class="primary-btn my_setting-nickname—-btn">确定</van-button>
  </div>
</template>
<script>
import Api from '@/api';
import { Toast } from 'vant';
import { mapActions, mapState } from 'vuex';

export default {
  data () {
    return {
      nickname: ''
    }
  },
  computed: {
    btnDisable() {
      return this.nickname.length <= 3;
    },
    ...mapState({
      isLoading: state => state.isLoading
    })
  },
  methods: {
    ...mapActions([
      'setNickname'
    ]),
    modifyNickname() {
      this.setNickname({
        nickname: this.nickname
      }).then(() => {
        Toast.success('修改成功');
        this.$router.go(-1);
      }).catch(err => {
        Toast.fail(err.message)
      })
    }
  }
}
</script>
