<template>
  <div class="setting-page">
    <img class="find-head-img" src="static/images/find_head_url.jpg" alt="">
    <!-- <van-nav-bar :title="title" class="nav-bar"/> -->
    <div class="find-navbar"><i class="h5-icon h5-icon-houtui"></i>EduSoho 微网校</div>
    <!-- <carousel></carousel> -->
    <course></course>
    <div class="find-footer">
      <div class="find-footer-item"
          v-for="item in items"
          :class="{ active: item.name === 'find' }"
          :style="{ width: `${100/items.length}%` }"
          :key="item.type">
        <img class="find-footer-item__icon"
              :src="item.name === 'find' ? item.active : item.normal"/>
        <span class="find-footer-item__text">{{ item.type }}</span>
      </div>
    </div>
    <!-- <img class="find-footer-img" src="static/images/find_bottom.jpg" alt=""> -->
  </div>
</template>

<script>
import items from '@/utils/footer-config'
import Api from '@admin/api';
import Carousel from './carousel';
import Course from './course';

export default {
  components: {
    'carousel': Carousel,
    'course': Course,
  },
  data() {
    return  {
      title: 'EduSoho 微网校',
      items,
    }
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
  }
}
</script>
