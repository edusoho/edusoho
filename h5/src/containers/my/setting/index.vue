<template>
  <div class="my_setting">
    <div class="my_setting-item" v-for="item in settings">
      <span class="my_setting-title">{{item.name}}</span>
      <div class="my_setting"></div>
    </div>
  </div>
</template>
<script>
export default {
  data() {
    return {
      settings: [{
        name: '头像',
        handler: this.updateAvatar
      }, {
        name: '昵称',
        handler: this.updateAvatar
      }, {
        name: '手机',
        handler: this.updateAvatar
      }]
    }
  },
  methods: {
    updateAvatar() {
      console.log('111');
    }
  }
}
</script>
