<template>
  <div class="goods">
    <div class="goods-detail">
      <!-- banner -->
      <div class="goods-detail__banner">
        <img :src="details.image" />
      </div>
      <!-- 优惠 -->
      <discount :details="details" />
      <!-- 商品名称、价格 -->
      <detail :details="details" :currentPlan="currentPlan" />
      <!-- 教学计划、有效期、服务 -->
      <specs
        :details="details"
        :currentPlan="currentPlan"
        @changePlan="changePlan"
      />
    </div>

    <div class="goods-info">
      <ul id="goods-info__nav" class="goods-info__nav">
        <li @click="onActive(0, 'introduction')">
          <a :class="active == 0 ? 'active' : ''" href="javascript:;">简介</a>
        </li>
        <li @click="onActive(1, 'teacher')">
          <a :class="active == 1 ? 'active' : ''" href="javascript:;">教师</a>
        </li>
        <li @click="onActive(2, 'catalog')">
          <a :class="active == 2 ? 'active' : ''" href="javascript:;">目录</a>
        </li>
        <li @click="onActive(3, 'evaluate')">
          <a :class="active == 3 ? 'active' : ''" href="javascript:;">评价</a>
        </li>
      </ul>

      <!-- 简介 -->
      <section class="js-scroll-top goods-info__item" id="introduction">
        <div class="goods-info__title">课程简介</div>
        <div class="info-introduction" v-html="summary"></div>
      </section>

      <!-- 教师 -->
      <section class="js-scroll-top goods-info__item" id="teacher">
        <div class="goods-info__title">教师风采</div>
        <teacher :teachers="componentsInfo.teachers" />
      </section>

      <!-- 目录： 课程和班级在这里的表现不一致，需要通过product.targetType来做变化，其他应该以数据为准 -->
      <section
        v-if="product.targetType === 'course'"
        class="js-scroll-top goods-info__item"
        id="catalog"
      >
        <div class="goods-info__title">课程目录</div>
        <!-- 课程详情 -->
        <afterjoin-directory />
      </section>

      <section
        v-if="product.targetType === 'classroom'"
        class="js-scroll-top goods-info__item"
        id="catalog"
      >
        <div class="goods-info__title">学习课程</div>
        <!-- 学习课程目录 -->
        <classroom-courses
          :classroomCourses="componentsInfo.classroomCourses"
        />
      </section>

      <!-- 评价 -->
      <section class="js-scroll-top goods-info__item" id="evaluate">
        <div class="goods-info__title">课程评价</div>
        <reviews
          :target-type="'goods'"
          :target-id="parseInt($route.params.id)"
          :limit="1"
        ></reviews>
      </section>

      <!-- 猜你想学 -->
      <section class="goods-info__item">
        <Recommend :recommendGoods="componentsInfo.recommendGoods">
          <span slot="title">猜你想学</span>
        </Recommend>
      </section>

      <!-- 收藏/购买 -->
      <buy />

      <!-- 回到顶部 -->
      <back-to-top v-show="backToTopShow" />
    </div>
  </div>
</template>

<script>
import Discount from './components/discount';
import Detail from './components/detail';
import Specs from './components/specs';

import Teacher from './components/teacher';
import Reviews from '@/containers/review';
import Recommend from './components/recommend';
import Buy from './components/buy';
import BackToTop from './components/back-to-top';
import AfterjoinDirectory from './components/afterjoin-directory';
import ClassroomCourses from './components/classroom-courses';

import Api from '@/api';
import { Toast } from 'vant';
import { mapActions } from 'vuex';

export default {
  data() {
    return {
      details: {},
      product: {},
      currentPlan: {}, // 当前学习计划
      active: 0, // 判断nav当前active
      timer: null,
      flag: true, // 点击取消滚动监听
      backToTopShow: false, // 是否显示回到顶部
      componentsInfo: {}, // 组件数据
    };
  },
  components: {
    Discount,
    Specs,
    Detail,
    Teacher, // 教师风采
    Reviews, // 课程评价
    Recommend, // 猜你想学
    Buy, // 购买按钮
    BackToTop, // 回到顶部
    AfterjoinDirectory,
    ClassroomCourses,
  },
  computed: {
    summary() {
      if (!this.details.description) return '暂无简介~';
      return this.details.description;
    },
  },
  methods: {
    ...mapActions('course', ['getCourseLessons']),
    getGoodsCourse() {
      Api.getGoodsCourse({
        query: {
          id: this.$route.params.id,
        },
      })
        .then(res => {
          const data = res;
          for (const key in data.specs) {
            this.$set(data.specs[key], 'active', false);
            this.$set(data.specs[key], 'id', key);
            if (key == this.$route.params.id) {
              this.$set(data.specs[key], 'active', true);
              this.currentPlan = data.specs[key];
            }
          }
          this.details = data;
          this.product = data.product;
        })
        .catch(err => {
          Toast.fail(err.message);
        });

      this.getCourseLessons({
        courseId: this.$route.params.id,
      }).then(res => {});
      this.getGoodsCourseComponents();
    },
    getGoodsCourseComponents() {
      Api.getGoodsCourseComponents({
        query: {
          id: this.$route.params.id,
        },
        params: {
          componentTypes: [
            'teachers',
            'reviews',
            'recommendGoods',
            'classroomCourses',
          ],
        },
      }).then(res => {
        this.componentsInfo = res;
      });
    },
    changePlan(id) {
      const data = this.details;
      for (const key in data.specs) {
        this.$set(data.specs[key], 'active', false);
        if (key == id) {
          this.$set(data.specs[key], 'active', true);
          this.currentPlan = data.specs[key];
        }
      }
    },
    onActive(value, eleId) {
      clearTimeout(this.timer);
      this.timer = null;
      this.flag = false;
      this.active = value;
      const eleTop = document.getElementById(eleId).offsetTop;
      const navHeight = document.getElementById('goods-info__nav').offsetHeight;
      document.documentElement.scrollTop = eleTop - navHeight;
      this.timer = setTimeout(() => {
        this.flag = true;
      }, 500);
    },
    handleScroll() {
      const scrollTop =
        document.documentElement.scrollTop || document.body.scrollTop;
      if (scrollTop > 600 && !this.backToTopShow) this.backToTopShow = true;
      if (scrollTop < 600 && this.backToTopShow) this.backToTopShow = false;
      if (!this.flag) return;
      clearTimeout(this.timer);
      this.timer = null;
      this.timer = setTimeout(() => {
        this.calcScrollTop(scrollTop);
      }, 200);
    },
    calcScrollTop(value) {
      const navHeight = document.getElementById('goods-info__nav').offsetHeight;
      const eleArr = document.querySelectorAll('.js-scroll-top');
      for (let i = eleArr.length - 1; i >= 0; i--) {
        if (value >= eleArr[i].offsetTop - navHeight) {
          if (this.active != i) this.active = i;
          return;
        } else {
          this.active = 0;
        }
      }
    },
  },
  created() {
    this.getGoodsCourse();
  },
  watch: {
    // 如果路由发生变化，再次执行该方法
    $route: 'getGoodsCourse',
  },
  mounted() {
    window.addEventListener('scroll', this.handleScroll);
  },
  destroyed() {
    window.removeEventListener('scroll', this.handleScroll);
  },
};
</script>
