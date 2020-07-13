<template>
  <div class="cd-container">
    <div class="product-breadcrumb">首页 / 艺术学概论</div>
    <!-- 信息 -->
    <detail :detailData="details" :currentPlan="currentPlan" :product="details.product" :is-favorite="componentsData.isFavorite" @changePlan="changePlan"></detail>

    <!--商品介绍、目录、评价、老师、二维码、猜你想学 -->
    <div class="product-info clearfix" v-show="Object.keys(details).length != 0">
      <div class="product-info__left info-left pull-left" :class="{'all-width': !details.hasExtension}">
        <div v-if="isFixed" class="fixed">
          <div class="cd-container clearfix">
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

          <!-- 商品介绍 -->
          <div id="info-left-1" class="content-item js-content-item">
            <h3 class="content-item__title">商品介绍</h3>
            <div v-html="descriptionHtml" style="padding-left: 14px; padding-top: 10px;"></div>
          </div>

          <!-- 学习目录 -->
          <div id="info-left-2" class="content-item js-content-item">
            <h3 class="content-item__title">学习目录</h3>
            <classroom-task />
          </div>

          <!-- 学员评价 -->
          <div id="info-left-3" class="info-left-reviews content-item js-content-item reviews">
            <h3 class="content-item__title">学员评价</h3>
            <!-- 创建评价 -->
            <create-review />
            <!-- 评价回复 -->
            <review v-for="review in componentsData.reviews" :key="review.id" :review="review" />
            <!-- 查看更多 -->
            <div class="learn-more"><a href="javascript:;">查看更多<i class="es-icon es-icon-chevronright"></i></a></div>
          </div>
        </div>
      </div>

      <div v-if="details.hasExtension" class="product-info__right pull-right">
        <!-- 授课老师 -->
        <teacher :teachers="componentsData.teachers" />
        <!-- 公众号 -->
        <qr :mpQrcode="componentsData.mpQrcode" />
        <!-- 猜你想学 -->
        <recommend :recommendGoods="componentsData.recommendGoods" />
      </div>
    </div>

    <!-- 回到顶部 -->
    <back-to-top v-show="isFixed" />
  </div>
</template>

<script>
  import axios from 'axios';
  import Detail from './components/detail';
  import CreateReview from './components/create-review';
  import Review from './components/review';
  import Teacher from './components/teacher';
  import Qr from './components/qr';
  import Recommend from './components/recommend';
  import BackToTop from './components/back-to-top';
  import ClassroomTask from './components/classroom-task'; // 班级课程列表

  export default {
    data() {
      return {
        details: {},
        currentPlan: {}, // 当前specs
        isFixed: false, // 是否吸顶
        howActive: 1, // 当前active
        flag: true,
        timerClick: null, // 延时器对象
        timerScroll: null,
        componentsData: {} // 评价、老师等数据
      }
    },
    components: {
      Detail,
      CreateReview,
      Review,
      Teacher,
      Qr,
      Recommend,
      BackToTop,
      ClassroomTask
    },
    computed: {
      descriptionHtml() {
        if (!this.description) return '暂无简介哦～';
        return this.description;
      }
    },
    methods: {
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
      },
      handleScroll() {
        let eleTop = this.$refs.infoLeftNav.offsetTop + this.$refs.infoLeftNav.offsetHeight;
        let scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
        if (!eleTop) return;
        if ( eleTop <= scrollTop && !this.isFixed ) this.isFixed = true;
        if ( eleTop > scrollTop && this.isFixed ) this.isFixed = false;
        clearTimeout(this.timerScroll);
        this.timerScroll = null;
        this.timerScroll = setTimeout(() => {
          if (this.flag) this.calcScrollTop(scrollTop);
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
        this.timerClick = null
        this.flag = false;
        this.howActive = value;
        let ele = '#info-left-' + value;
        document.documentElement.scrollTop = $(ele).offset().top - 80;
        this.timerClick = setTimeout(() => {
         this.flag = true;
        }, 300);
      },
      requestExtensions() {
        axios.get('/api/goods/1/components', {
          params: {
            componentTypes: ['teachers', 'mpQrcode', 'recommendGoods', 'reviews']
          },
          headers: {
            'Accept': 'application/vnd.edusoho.v2+json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': $('meta[name=csrf-token]').attr('content')
          }
        }).then(res => {
          this.componentsData = res.data;
        });
      },
      requestDetails() {
        let goodsId = window.location.pathname.replace(/[^0-9]/ig, ""); // goods id
        axios.get('/api/goods/' +　goodsId, {
          headers: { 'Accept': 'application/vnd.edusoho.v2+json'}
        }).then((res) => {
          let data = res.data;
          for (const key in data.specs) {
            this.$set(data.specs[key], 'active', false);
            this.$set(data.specs[key], 'id', key);
            if (key == goodsId) {
              this.$set(data.specs[key], 'active', true);
              this.currentPlan = data.specs[key];
            }
          }
          this.details = data;
        });
      }
    },
    created() {
      this.requestDetails();
      this.requestExtensions();
    },
    mounted() {
      window.addEventListener("scroll", this.handleScroll);
    },
    destroyed() {
      window.removeEventListener('scroll', this.handleScroll);
    }
  }
</script>
