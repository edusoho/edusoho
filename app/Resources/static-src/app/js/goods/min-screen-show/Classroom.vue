<template>
  <div class="goods">

    <!-- banner -->
    <div class="goods-banner">
      <img :src="details.image">
    </div>

    <!-- 优惠活动 -->
    <goods-discount />

    <!-- 名称、价格 -->
    <goods-information :details="details" :currentPlan="currentPlan" />

    <!-- 教学计划、有效期、承诺服务 -->
    <goods-specs :details="details" :currentPlan="currentPlan" @changePlan="changePlan" />

    <div class="goods-info">
      <ul id="goods-info__nav" class="goods-info__nav">
        <li @click="onActive(0, 'introduction')"><a :class="active == 0 ? 'active' : ''" href="javascript:;">简介</a></li>
        <li @click="onActive(1, 'teacher')"><a :class="active == 1 ? 'active' : ''" href="javascript:;">教师</a></li>
        <li @click="onActive(2, 'catalog')"><a :class="active == 2 ? 'active' : ''" href="javascript:;">课程</a></li>
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
        <goods-teacher :teachers="componentsInfo.teachers" />
      </section>

      <!-- 目录 -->
      <section class="js-scroll-top goods-info__item" id="catalog">
        <div class="goods-info__title">课程目录</div>
        <!-- 任务列表 -->
        <classroom-catalog></classroom-catalog>
      </section>

      <!-- 评价 -->
      <section class="js-scroll-top goods-info__item" id="evaluate">
        <div class="goods-info__title">课程评价</div>
        <goods-reviews :reviews="componentsInfo.reviews" />
      </section>

      <!-- 猜你想学 -->
      <section class="goods-info__item">
        <goods-learn :recommendGoods="componentsInfo.recommendGoods">
          <span slot="title">猜你想学</span>
        </goods-learn>
      </section>

      <!-- 收藏/购买 -->
      <goods-buy />

      <!-- 回到顶部 -->
      <back-to-top v-show="backToTopShow" />
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import GoodsDiscount from './components/goods-discount'; // 优惠活动
import GoodsInformation from './components/goods-information'; // 名称、价格、在学人数
import GoodsSpecs from './components/goods-specs'; // 学习计划、有效期、承诺服务
import GoodsTeacher from './components/goods-teacher'; // 老师信息
import GoodsReviews from './components/goods-reviews'; // 评价
import GoodsLearn from './components/goods-learn'; // 推荐学习
import GoodsBuy from './components/goods-buy'; // 购买按钮
import BackToTop from './components/back-to-top'; // 回到顶部
import ClassroomCatalog from './components/classroom-catalog'; // 班级课程

export default {
  data() {
    return {
      details: {}, // 课程数据
      currentPlan: {}, // 当前教学计划
      componentsInfo: {}, // 组件数据
      active: 0, // 判断nav当前active
      timer: null,
      flag: true, // 点击取消滚动监听
      backToTopShow: false, // 是否显示回到顶部
    }
  },
  components: {
    GoodsDiscount,
    GoodsInformation,
    GoodsSpecs,
    GoodsTeacher,
    GoodsReviews,
    GoodsLearn,
    GoodsBuy,
    BackToTop,
    ClassroomCatalog
  },
  computed: {
    summary() {
      if (!this.details.description) return '暂无简介~';
      return this.details.description;
    }
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
    // 获取商品信息
    getGoodsDetails() {
      let routerNum = 2; // goods id
      axios.get('/api/goods/1', {
        headers: { 'Accept': 'application/vnd.edusoho.v2+json'}
      }).then((res) => {
        let data = res.data;
        for (const key in data.specs) {
          this.$set(data.specs[key], 'active', false);
          this.$set(data.specs[key], 'id', key);
          if (key == routerNum) {
            this.$set(data.specs[key], 'active', true);
            this.currentPlan = data.specs[key];
          }
        }
        this.details = data;
      });
    },
    getComponentsInfo() {
      axios.get('/api/goods/1/components', {
        params: {
          componentTypes: ['teachers', 'recommendGoods', 'reviews']
        },
        headers: {
          'Accept': 'application/vnd.edusoho.v2+json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-Token': $('meta[name=csrf-token]').attr('content')
        }
      }).then(res => {
        this.componentsInfo = res.data;
      });
    },
    changePlan(id) {
      let data = this.details;
      for (const key in data.specs) {
        this.$set(data.specs[key], 'active', false);
        if (key == id) {
          this.$set(data.specs[key], 'active', true);
          this.currentPlan = data.specs[key];
        }
      }
      this.details = data;
    }
  },
  created() {
    this.getGoodsDetails();
    this.getComponentsInfo();
  },
  mounted() {
    window.addEventListener("scroll", this.handleScroll);
  },
  destroyed() {
    window.removeEventListener('scroll', this.handleScroll);
  }
}
</script>