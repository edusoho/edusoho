<template>
  <div class="my_setting">
    <div class="my_setting-item" v-for="(item, index) in settings" @click="handleSetting(index)">
      <span class="my_setting-title title-18">{{item.name}}</span>
      <div class="my_setting-content">
        <img src="" alt="" v-if="!index" class="my_setting-avatar">
        <img src="/static/images/more.png" alt="" class="my_setting-more">
      </div>
      <van-uploader :after-read="onRead" v-if="!index"></van-uploader>
    </div>
  </div>
</template>
<script>
export default {
  data() {
    return {
      settings: [{
        name: '头像',
      }, {
        name: '昵称',
      }, {
        name: '手机',
      }]
    }
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
