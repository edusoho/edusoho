<template>
  <div class="product-info clearfix">
    <div class="product-info__left info-left pull-left" :class="{'all-width': !infoData.hasExtension}">
      <div v-if="isFixed" class="fixed">
        <div class="cd-container clearfix" >
          <ul class="info-left__nav pull-left">
            <li :class="howActive == 1 ? 'active' : ''"><a href="javascript:;" @click="clickType(1)">商品介绍</a></li>
            <li :class="howActive == 2 ? 'active' : ''"><a href="javascript:;" @click="clickType(2)">学习目录</a></li>
            <li :class="howActive == 3 ? 'active' : ''"><a href="javascript:;" @click="clickType(3)">学员评价</a></li>
          </ul>
          <div class="buy__btn pull-right">
            <a href="javascript:;">立即购买</a>
          </div>
        </div>
      </div>
      <ul class="info-left__nav" ref="infoLeftNav">
        <li :class="howActive == 1 ? 'active' : ''"><a href="javascript:;" @click="clickType(1)">商品介绍</a></li>
        <li :class="howActive == 2 ? 'active' : ''"><a href="javascript:;" @click="clickType(2)">学习目录</a></li>
        <li :class="howActive == 3 ? 'active' : ''"><a href="javascript:;" @click="clickType(3)">学员评价</a></li>
      </ul>
      <div class="info-left__content">
        <!-- <div v-if="isFixed" class="fixed-box"></div> -->
        <div id="info-left-1" class="content-item js-content-item">
          <h3 class="content-item__title">商品介绍</h3>
        </div>
        <div id="info-left-2" class="content-item js-content-item">
          <h3  class="content-item__title">学习目录</h3>
        </div>
        <div id="info-left-3" class="content-item js-content-item">
          <h3  class="content-item__title">学员评价</h3>
        </div>
      </div>
    </div>
    <div v-if="infoData.hasExtension" class="product-info__right pull-right">
      <!-- 授课老师 -->
      <info-right-teacher :teachers="componentsData.teachers"></info-right-teacher>
      <!-- 公众号 -->
      <info-right-qr :mpQrCode="componentsData.mpQrCode"></info-right-qr>
      <!-- 猜你想学 -->
      <info-right-learn :recommendGoods="componentsData.recommendGoods"></info-right-learn>
    </div>
  </div>
</template>

<script>
  import axios from 'axios';
  import infoRightTeacher from './info-right-teacher';
  import infoRightQr from './info-right-qr';
  import infoRightLearn from './info-right-learn';
  export default {
    data() {
      return {
        isFixed: false, // 是否吸顶
        howActive: 1, // 当前active
        flag: true,
        timer: null, // 延时器对象
        componentsData: {}
      }
    },
    props: {
      infoData: {
        type: Object,
        default: function () {
          return {}
        }
      }
    },
    components: {
      infoRightTeacher,
      infoRightQr,
      infoRightLearn
    },
    methods: {
      handleScroll() {
        let eleTop = this.$refs.infoLeftNav.offsetTop + this.$refs.infoLeftNav.offsetHeight;
        let scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
        if ( eleTop <= scrollTop && !this.isFixed ) this.isFixed = true;
        if ( eleTop > scrollTop && this.isFixed ) this.isFixed = false;
        clearTimeout(this.timer);
        this.timer = null
        this.timer = setTimeout(() => {
          this.calcScrollTop(scrollTop);
        }, 200);
      },
      calcScrollTop(value) {
        let eleArr = $('.js-content-item');
        for (let i = eleArr.length - 1; i >= 0; i--) {
          const elementTop = eleArr[i].offsetTop - 80;
          if (value >= elementTop) {
            if (this.howActive != i + 1) this.howActive = i + 1;
            return;
          } else {
            this.howActive = 1;
          }
        }
      },
      clickType(value) {
        clearTimeout(this.timer);
        this.timer = null
        this.flag = false;
        this.howActive = value;
        let ele = '#info-left-' + value;
        document.documentElement.scrollTop = $(ele).offset().top - 80;
        this.timer = setTimeout(() => {
         this.flag = true;
        }, 500);
      },
      requestExtensions(extensions) {
        axios.get('/api/goods/1/components', {
          params: {
            componentTypes: extensions
          },
          headers: {
            'Accept': 'application/vnd.edusoho.v2+json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': $('meta[name=csrf-token]').attr('content')
          }
        }).then(res => {
          this.componentsData = res.data;
        });
      }
    },
    watch: {
      infoData(newValue, oldValue) {
        this.requestExtensions(newValue.extensions);
      }
    },
    mounted() {
      window.addEventListener("scroll", this.handleScroll);
    },
    destroyed() {
      window.removeEventListener('scroll', this.handleScroll);
    }
  }
</script>