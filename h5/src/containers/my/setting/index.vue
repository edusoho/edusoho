<template>
  <div class="my_setting">
    <div class="my_setting-item" v-for="(item, index) in settings" @click="handleSetting(index)">
      <span class="my_setting-title title-18">{{item.name}}</span>
      <div class="my_setting-content">
        <img :src="user.avatar.small" alt="" v-if="!index" class="my_setting-avatar">
        <span  v-if="index">{{item.info}}</span>
        <img src="/static/images/more.png" alt="" class="my_setting-more">
      </div>
      <van-uploader :after-read="onRead" v-if="!index"></van-uploader>
    </div>
  </div>
</template>
<script>
import { mapState } from 'vuex';
import { Toast } from 'vant';

export default {
  data() {
    return {
      settings: [{
        name: '头像',
        info: '',
      }, {
        name: '昵称',
        info: '1111'
      }, {
        name: '手机',
        info: '122222222222'
      }]
    }
  },
  computed: {
    ...mapState({
      user: state => state.user
    })
  },
  created() {
    console.log(this.user);
    this.$set(this.settings[0], 'info', this.user.avatar.small);
    this.$set(this.settings[1], 'info', this.user.nickname);
     this.$set(this.settings[2], 'info', this.user.school);
  },
  methods: {
    handleSetting(index) {
      switch(index) {
        case 0:
          break;
        case 1:
          this.$router.push('/setting/nickname');
          break;
        case 2:
          Toast('更改手机号，后续开通');
          break;
        default:
          break;
      }
    },
    onRead(file) {
      console.log(file)
    }
  }
}
</script>
