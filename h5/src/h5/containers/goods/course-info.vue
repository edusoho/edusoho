<template>
  <div class="goods-info">
    <ul id="goods-info__nav" class="goods-info__nav">
      <li @click="onActive(0, 'introduction')"><a :class="active == 0 ? 'active' : ''" href="javascript:;">简介</a></li>
      <li @click="onActive(1, 'teacher')"><a :class="active == 1 ? 'active' : ''" href="javascript:;">教师</a></li>
      <li @click="onActive(2, 'catalog')"><a :class="active == 2 ? 'active' : ''" href="javascript:;">目录</a></li>
      <li @click="onActive(3, 'evaluate')"><a :class="active == 3 ? 'active' : ''" href="javascript:;">评价</a></li>
    </ul>

    <!-- 简介 -->
    <section class="js-scroll-top goods-info__item" id="introduction">
      <div class="goods-info__title">课程简介</div>
      <div class="info-introduction" v-html="summary"></div>
    </section>

    <!-- 教师 -->
    <section class="js-scroll-top goods-info__item" id="teacher">
      <div class="goods-info__title">教师风采</div>
      <info-teacher :teachers="componentsInfo.teachers" />
    </section>

    <!-- 目录 -->
    <section class="js-scroll-top goods-info__item" id="catalog">
      <div class="goods-info__title">课程目录</div>
      <!-- 课程详情 -->
      <afterjoin-directory />
    </section>

    <!-- 评价 -->
    <section class="js-scroll-top goods-info__item" id="evaluate">
      <div class="goods-info__title">课程评价</div>
      <info-evaluate :reviews="componentsInfo.reviews" />
    </section>

    <!-- 猜你想学 -->
    <section class="goods-info__item">
      <info-learn :recommendGoods="componentsInfo.recommendGoods">
        <span slot="title">猜你想学</span>
      </info-learn>
    </section>

    <!-- 收藏/购买 -->
    <info-buy />

    <!-- 回到顶部 -->
    <back-to-top v-show="backToTopShow" />
  </div>
</template>

<script>
import InfoTeacher from './components/info-teacher';
import InfoEvaluate from './components/info-evaluate';
import InfoLearn from './components/info-learn';
import InfoBuy from './components/info-buy';
import BackToTop from './components/back-to-top';
import AfterjoinDirectory from './components/afterjoin-directory';
import Api from '@/api';
export default {
  data() {
    return {
      active: 0, // 判断nav当前active
      timer: null,
      flag: true, // 点击取消滚动监听
      backToTopShow: false, // 是否显示回到顶部
      componentsInfo: {} // 组件数据
    }
  },
  props: {
    details: {
      type: Object,
      default: () => {}
    }
  },
  computed: {
    summary() {
      if (!this.details.description) return '暂无简介~';
      return this.details.description;
    }
  },
  components: {
    InfoTeacher, // 教师风采
    InfoEvaluate, // 课程评价
    InfoLearn, // 猜你想学
    InfoBuy, // 购买按钮
    BackToTop, // 回到顶部
    AfterjoinDirectory
  },
  methods: {
    onActive(value, eleId) {
      clearTimeout(this.timer);
      this.timer = null
      this.flag = false;
      this.active = value;
      let eleTop = document.getElementById(eleId).offsetTop;
      let navHeight =  document.getElementById('goods-info__nav').offsetHeight;
      document.documentElement.scrollTop = eleTop - navHeight;
      this.timer = setTimeout(() => {
        this.flag = true;
      }, 500);
    },
    handleScroll() {
      let scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
      if (scrollTop > 600 && !this.backToTopShow) this.backToTopShow = true;
      if (scrollTop < 600 && this.backToTopShow) this.backToTopShow = false;
      if (!this.flag) return;
      clearTimeout(this.timer);
      this.timer = null
      this.timer = setTimeout(() => {
        this.calcScrollTop(scrollTop);
      }, 200);
    },
    calcScrollTop(value) {
      let navHeight =  document.getElementById('goods-info__nav').offsetHeight;
      let eleArr = document.querySelectorAll('.js-scroll-top');
      for (let i = eleArr.length - 1; i >= 0; i--) {
        if (value >= eleArr[i].offsetTop - navHeight) {
          if (this.active != i) this.active = i;
          return;
        } else {
          this.active = 0;
        }
      }
    },
    getGoodsCourseComponents() {
      Api.getGoodsCourseComponents({
        query: {
          id: this.$route.params.id
        },
        params: {
          componentTypes: ['teachers', 'reviews', 'recommendGoods']
        }
      }).then(res => {
        this.componentsInfo = res;
      });

    }
  },
  watch: {
    // 如果路由发生变化，再次执行该方法
    "$route": "getGoodsCourseComponents"
  },
  created() {
    this.getGoodsCourseComponents();
  },
  mounted() {
    window.addEventListener("scroll", this.handleScroll);
  },
  destroyed() {
    window.removeEventListener('scroll', this.handleScroll);
  }
}
</script>