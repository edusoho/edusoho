<template>
  <div>
    home
    <router-view></router-view>
    <van-tabbar v-model="active">
      <van-tabbar-item icon="shop">发现</van-tabbar-item>
      <van-tabbar-item icon="chat" dot>学习</van-tabbar-item>
      <van-tabbar-item icon="records" info="5">我的</van-tabbar-item>
    </van-tabbar>
  </div>
</template>

<script>
  export default {
    data () {
      return {
        active: 0
      };
    },
  }

</script>
