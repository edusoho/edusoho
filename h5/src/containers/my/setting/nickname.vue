<template>
  <div class="my_setting-nickname">
    <e-loading v-if="isLoading"></e-loading>
    <van-field v-model="nickname" placeholder="请修改您的用户名" class="my_setting-nickname--input"/>

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
      nickname: '',
      confirmFlag: false,
    }
  },
  computed: {
    btnDisable() {
      return this.nickname.length <= 0;
    },
    ...mapState({
      isLoading: state => state.isLoading
    })
  },
  watch: {
    nickname() {
      const reg = /^([\u4E00-\uFA29]|[a-zA-Z0-9_.·])*$/i;
      if (!reg.test(this.nickname)) {
        Toast('仅支持中文字、英文字母、数字及_ . ·');
        this.confirmFlag = false;
      } else {
        this.confirmFlag = true;
      }
    }
  },
  methods: {
    ...mapActions([
      'setNickname'
    ]),
    modifyNickname() {
      if(this.confirmFlag) {
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
}
</script>
