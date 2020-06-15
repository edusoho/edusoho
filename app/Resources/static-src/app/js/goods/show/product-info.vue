<template>
  <div class="product-info clearfix">
    <div class="product-info__left info-left pull-left">
      <div v-if="isFixed" class="fixed">
        <div class="cd-container clearfix" >
          <ul class="info-left__nav pull-left">
            <li :class="howActive == 1 ? 'active' : ''"><a href="#info-left-1">商品介绍</a></li>
            <li :class="howActive == 2 ? 'active' : ''"><a href="#info-left-2">学习目录</a></li>
            <li :class="howActive == 3 ? 'active' : ''"><a href="#info-left-3">学员评价</a></li>
          </ul>
          <div class="buy__btn pull-right">
            <a href="javascript:;">立即购买</a>
          </div>
        </div>
      </div>
      <ul class="info-left__nav" ref="infoLeftNav">
        <li :class="howActive == 1 ? 'active' : ''"><a href="#info-left-1">商品介绍</a></li>
        <li :class="howActive == 2 ? 'active' : ''"><a href="#info-left-2">学习目录</a></li>
        <li :class="howActive == 3 ? 'active' : ''"><a href="#info-left-3">学员评价</a></li>
      </ul>
      <div class="info-left__content">
        <div v-if="isFixed" class="fixed-box"></div>
        <h3 id="info-left-1" class="info-left__content__title">商品介绍</h3>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <h3 id="info-left-2" class="info-left__content__title">学习目录</h3>
           <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
           <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <h3 id="info-left-3" class="info-left__content__title">学员评价</h3>
           <p>text</p>
        <p>text</p>
        <p>text</p>
           <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
        <p>text</p>
      </div>
    </div>
    <div class="product-info__right pull-right">
      <!-- 授课老师 -->
      <info-right-teacher></info-right-teacher>
      <!-- 公众号 -->
      <info-right-qr></info-right-qr>
      <!-- 猜你想学 -->
      <info-right-learn></info-right-learn>
    </div>
  </div>
</template>

<script>
  import infoRightTeacher from './info-right-teacher';
  import infoRightQr from './info-right-qr';
  import infoRightLearn from './info-right-learn';
  export default {
    data() {
      return {
        isFixed: false,
        howActive: 1
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