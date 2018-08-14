<template>
  <div class="setting-page">
    <img class="find-head-img" src="static/images/find_head_url.jpg" alt="">
    <van-nav-bar :title="title" class="nav-bar"/>
    <div class="find-page__part" v-for="part in parts">
      <swipe :slides="part.data"></swipe>
    </div>
    <img class="find-footer-img" src="static/images/find_bottom.jpg" alt="">
  </div>
</template>

<script>
import Api from '@/api'
import Swipe from '@admin/containers/components/e-swipe/e-swipe.vue';
import 'vue-swipe/dist/vue-swipe.css';

export default {
  components: {
    'swipe': Swipe,
  },
  data() {
    return  {
      title: 'EduSoho 微网校',
      showLeftArrow: false,
      parts: [{
        data:[
          {
            image: 'http://zyc.st.edusoho.cn/themes/jianmo/img/banner_app.jpg',
            link: {
              type: 'url',
              url: 'http://zyc.st.edusoho.cn'
            }
          },
          {
            image: 'http://zyc.st.edusoho.cn/themes/jianmo/img/banner_app.jpg',
            link: {
              type: 'url',
              url: 'http://zyc.st.edusoho.cn'
            }
          }
        ],
        moduleType: "slide",
        type: "slide_show"
      }]
    }
  }
}
</script>
