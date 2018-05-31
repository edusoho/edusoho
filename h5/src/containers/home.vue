<template>
  <div class="home">
    <van-tabbar v-model="active">
      <van-tabbar-item icon="shop">发现</van-tabbar-item>
      <van-tabbar-item icon="chat">学习</van-tabbar-item>
      <van-tabbar-item icon="records">我的</van-tabbar-item>
    </van-tabbar>
    <keep-alive>
      <router-view></router-view>
    </keep-alive>
  </div>
</template>
<script>
export default {
  data() {
    return {
      active: 0
    }
  }
}
</script>

<style lang="less">

</style>

