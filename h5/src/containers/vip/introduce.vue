<template>
  <div class="vip-introduce">
    <van-swipe :width="260" :show-indicators="false" :loop="false" :touchable="true">
      <van-swipe-item v-for="img, index in bgImgs" :key="index">
        <div class="ph10">
          <img class="card-bg-img" :src="img" alt="">
        </div>
     </van-swipe-item>
    </van-swipe>
  </div>
</template>

<script>

  export default {
    data() {
      return {
        bgImgs: [
          'http://lvliujie.st.edusoho.cn/files/default/2018/12-18/111534665c85317473.png',
          'http://lvliujie.st.edusoho.cn/files/default/2018/12-18/111534665c85317473.png',
          'http://lvliujie.st.edusoho.cn/files/default/2018/12-18/111534665c85317473.png'
        ]
      }
    }
  }

</script>