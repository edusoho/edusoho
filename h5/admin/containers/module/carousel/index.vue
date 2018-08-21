<template>
  <div class="setting-carousel">
    <div class="find-page__part" v-for="part in parts">
      <swipe :slides="part.data"></swipe>
    </div>
    <div class="carousel-allocate">
      <header class="title">轮播图设置</header>
      <div class="carousel-item-setting clearfix">
        <div class="add-img"><i class="text-18">+</i> 添加图片</div>
        <div class="add-title f-left">标题：<input type="text"></div>
        <div class="f-left">链接：<input type="text"></div>
      </div>
    </div>
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
      parts: [{
        data:[
          {
            image: 'http://zyc.st.edusoho.cn/themes/jianmo/img/banner_app.jpg',
            link: {
              type: 'url',
              // url: 'http://zyc.st.edusoho.cn'
            }
          },
          {
            image: 'http://zyc.st.edusoho.cn/themes/jianmo/img/banner_app.jpg',
            link: {
              type: 'url',
              // url: 'http://zyc.st.edusoho.cn'
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
