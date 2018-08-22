<template>
  <div class="setting-carousel">
   <!--  <div class="find-page__part" v-for="part in parts">
      <swipe :slides="part.data"></swipe>
    </div> -->
    <div class="carousel-allocate">
      <header class="title">课程列表设置</header>
      <div class="carousel-item-setting clearfix">
        <div class="add-img"><i class="text-18">+</i> 添加图片</div>
        <div class="add-title f-left">标题：<input type="text"></div>
        <div class="f-left">链接：<input type="text"></div>
      </div>
    </div>
  </div>
</template>


<script>

export default {
  components: {
  },
  data() {
    return  {
    }
  }
}
</script>
