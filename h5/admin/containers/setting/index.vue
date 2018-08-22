<template>
  <div class="setting-page">
    <img class="find-head-img" src="static/images/find_head_url.jpg" alt="">
    <van-nav-bar :title="title" class="nav-bar"/>
    <carousel></carousel>
    <img class="find-footer-img" src="static/images/find_bottom.jpg" alt="">
  </div>
</template>

<script>
import Carousel from '@admin/containers/module/carousel/index.vue';

export default {
  components: {
    'carousel': Carousel,
  },
  data() {
    return  {
      title: 'EduSoho 微网校',
    }
  }
}
</script>
