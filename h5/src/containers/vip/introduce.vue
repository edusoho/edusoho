<template>
  <div class="vip-introduce">
    <van-swipe :width="210" :show-indicators="false" :touchable="true" @change="onChange">
      <van-swipe-item v-for="img, index in bgImgs" :key="index" :swipeTo="index" :class="activeIndex == index ? 'active' : ''">
        <img class="card-bg-img" :src="img" alt="">
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
        ],
        activeIndex: 1
      }
    },
    methods: {
      onChange(index) {
        this.activeIndex = index + 1;
        console.log(222,index)
      }
    }
  }

</script>