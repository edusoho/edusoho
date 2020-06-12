<template>
  <div class="info-right-learn info-right-box">
    <div class="title">猜你想学</div>
    <div class="learn-info">
      <div class="learn-info-item learn clearfix" v-for="(item, index) in arr" :key="index">
        <div class="learn-img pull-left">
          <img src="http://t5.edusoho.cn/files/default/2019/08-15/14354956e275898429.jpeg" alt="">
        </div>
        <div class="learn-text pull-right">
          <p class="learn-text__title">摄影进阶班：新概念课创作理念课第一节课…</p>
          <span class="learn-text__price">129.00</span>
        </div>
      </div>
    </div>
    <div class="learn-more"><a href="javascript:;">查看更多<i class="es-icon es-icon-chevronright"></i></a></div>
  </div>
</template>

<script>
 export default {
    data() {
      return {
        arr: [1, 2, 3, 4, 5, 6]
      }
    }
  }
</script>