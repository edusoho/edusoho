<template>
  <div class="setting-page">
    <img class="find-head-img" src="static/images/find_head_url.jpg" alt="">
    <van-nav-bar :title="title" class="nav-bar"/>
    <carousel></carousel>
    <course></course>
    <img class="find-footer-img" src="static/images/find_bottom.jpg" alt="">
  </div>
</template>

<script>
import Api from '@admin/api';
import Carousel from '@admin/containers/module/carousel/index.vue';
import Course from '@admin/containers/module/course/index.vue';

export default {
  components: {
    'carousel': Carousel,
    'course': Course,
  },
  created() {
    Api.saveDraftDate({
      query: {
        portal: 'h5',
        type : 'discovery'
      },
      params: {
        mode: 'draft'
      }
    })
    .then((data) => {
      console.log(data)
      this.parts = data;
    })
    .catch((err) => {
      console.log(err, 'error');
    });
  },
  data() {
    return  {
      title: 'EduSoho 微网校',
    }
  }
}
</script>
