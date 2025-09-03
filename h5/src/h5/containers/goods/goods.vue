<template>
  <div>
    <e-loading v-if="isLoading" />
    <div class="goods" v-if="goods.id">
      <div class="goods-detail">
        <div class="goods-detail__banner">
          <img :src="goods.images.large" />
        </div>

        <discount
          v-if="goods.hidePrice !== '1' && goods.discount"
          :currentSku="currentSku"
          :goods="goods"
        />

        <detail
          :goods="goods"
          :currentSku="currentSku"
          :goods-setting="goodsSetting"
        />

        <vip
          v-if="goods.hidePrice !== '1' && currentSku.vipLevelInfo && vipSwitch"
          :currentSku="currentSku"
          :type="goods.type"
        />
        <specs
          v-if="goods.specs.length > 1 || currentSku.services"
          :goods="goods"
          :currentSku="currentSku"
          @changeSku="changeSku"
        />

        <certificate
          v-if="currentSku.hasCertificate"
          :selectedPlanId="currentSku.targetId"
        />

        <enter-learning
          v-if="
            componentsInfo.mpQrCode &&
              Object.keys(componentsInfo.mpQrCode).length
          "
          :qr-info="componentsInfo.mpQrCode"
        />
      </div>

      <div class="goods-info">
        <ul id="goods-info__nav" class="goods-info__nav">
          <li @click="onActive(0, 'introduction')">
            <a :class="active == 0 ? 'active' : ''" href="javascript:;">{{ $t('goods.intro') }}</a>
          </li>
          <li @click="onActive(1, 'teacher')">
            <a :class="active == 1 ? 'active' : ''" href="javascript:;">{{ $t('goods.teacher') }}</a>
          </li>
          <li @click="onActive(2, 'bindItemBank')">
            <a :class="active == 2 ? 'active' : ''" href="javascript:;">题库</a>
          </li>
          <li @click="onActive(3, 'catalog')">
            <a :class="active == 3 ? 'active' : ''" href="javascript:;">{{ $t('goods.catalogue') }}</a>
          </li>
          <li
            @click="onActive(4, 'evaluate')"
            v-if="
              (show_course_review == 1 && goods.type === 'course') ||
                (show_classroom_review == 1 && goods.type === 'classroom')
            "
          >
            <a :class="active == 4 ? 'active' : ''" href="javascript:;">{{ $t('goods.comment') }}</a>
          </li>
        </ul>

        <!-- 简介 -->
        <section class="js-scroll-top goods-info__item" id="introduction">
          <div class="goods-info__title">{{ $t('goods.intro') }}</div>
          <div class="info-introduction" v-html="summary"></div>
        </section>

        <!-- 教师 -->
        <section class="js-scroll-top goods-info__item" id="teacher">
          <div class="goods-info__title">{{ $t('goods.teacherStyle') }}</div>
          <teacher :teachers="currentSku.teachers" />
        </section>

        <!-- 绑定的题库-->
        <section class="js-scroll-top goods-info__item flex flex-col space-y-12" id="bindItemBank">
          <div class="flex justify-between">
            <div class="goods-info__title">题库</div>
            <div v-if="bindItemBankList.length > 2" class="flex items-center goods-info__title" style="color: #919399; font-weight: 400;" @click="goItemBank">
              <div class="">{{ `查看全部（${bindItemBankList.length}）` }}</div>
              <van-icon name="arrow" />
            </div>
          </div>
          <div v-if="bindItemBankList.length === 0">
            <div class="goods-info__title" style="color: #919399; font-weight: 400;">暂无题库</div>
          </div>
          <div v-else class="space-y-12">
            <goods-item-bank :item="bindItemBankList[0]" :hidePrice = "goods.hidePrice" />
            <goods-item-bank v-if="bindItemBankList.length > 1" :item="bindItemBankList[1]" :hidePrice = "goods.hidePrice" />
          </div>
        </section>

        <!-- 目录： 课程和班级在这里的表现不一致，需要通过product.targetType来做变化，其他应该以数据为准 -->
        <section
          v-if="goods.product.targetType === 'course'"
          class="js-scroll-top goods-info__item"
          id="catalog"
        >
          <div class="goods-info__title">{{ $t('goods.tableOfContents') }}</div>
          <!-- 课程详情 -->
          <afterjoin-directory v-if="currentSku.taskDisplay == 1" :goods="goods" />
          <div class="goods-empty-content" v-else>
            <img src="static/images/goods/empty-content.png" alt="">
            <p>{{ $t('goods.tableOfContentsEmpty') }}</p>
          </div>
        </section>

        <section
          v-if="goods.product.targetType === 'classroom'"
          class="js-scroll-top goods-info__item"
          id="catalog"
        >
          <div class="flex justify-between items-center">
            <div class="goods-info__title">{{ $t('goods.learningCatalog') }}</div>
            <div class="flex items-center bg-fill-7 py-4 px-12 rounded-full" @click="goSearch">
              <IconSearch />
              <label class="ml-4 text-text-6">{{ $t('e.searchCourse') }}</label>
            </div>
          </div>
          <!-- 学习课程目录 -->
          <classroom-courses v-if="componentsInfo.classroomCourses.length > 0"
            :classroomCourses="componentsInfo.classroomCourses" :hidePrice = "goods.hidePrice"
          />
          <div class="w-full flex flex-col items-center pt-28" style="height: 223px;" v-else>
            <img style="width: 142px; height: 116px;" src="static/images/classroom/none-course.png" alt="暂无课程" />
            <span class="mt-12 text-text-6 text-14">暂无课程</span>
          </div>
        </section>

        <!-- 评价 -->
        <section
          class="js-scroll-top goods-info__item"
          id="evaluate"
          v-if="
            (show_course_review == 1 && goods.type == 'course') ||
              (show_classroom_review == 1 && goods.type == 'classroom')
          "
        >
          <div class="goods-info__title">{{ $t('goods.courseEvaluation') }}</div>
          <reviews
            v-if="
              (show_course_review == 1 && goods.type == 'course') ||
                (show_classroom_review == 1 && goods.type == 'classroom')
            "
            :target-type="'goods'"
            :target-id="parseInt($route.params.id)"
            :limit="5"
          ></reviews>
          <div v-else class="info-introduction">
            {{ $t('goods.noContent') }}
          </div>
        </section>

        <!-- 猜你想学 -->
        <section class="goods-info__item">
          <Recommend
            :goods="goods"
            :recommendGoods="
              componentsInfo.recommendGoods
                ? componentsInfo.recommendGoods.slice(0, 4)
                : componentsInfo.recommendGoods
            "
          >
            <span slot="title">{{ $t('goods.guessYouWantToLearn') }}</span>
          </Recommend>
        </section>

        <!-- 收藏/购买 -->
        <buy
          :goods="goods"
          :currentSku="currentSku"
          :is-favorite="goods.isFavorite"
          @update-data="updateFavorite"
        />

        <!-- 回到顶部 -->
        <back-to-top v-show="backToTopShow" />
      </div>
    </div>
  </div>
</template>

<script>
import Discount from './components/discount';
import Detail from './components/detail';
import Specs from './components/specs';
import Certificate from './components/certificate';
import Vip from './components/vip';
import EnterLearning from './components/enter-learning';
import IconSearch from '&/components/IconSvg/IconSearch.vue';
import Teacher from './components/teacher';
import Reviews from '@/containers/review';
import Recommend from './components/recommend';
import Buy from './components/buy';
import BackToTop from './components/back-to-top';
import AfterjoinDirectory from './components/afterjoin-directory';
import ClassroomCourses from './components/classroom-courses';
import initShare from '@/utils/weiixn-share-sdk';
import GoodsItemBank from '@/components/item-bank/goods-item-bank.vue';

import Api from '@/api';
import {Toast} from 'vant';
import {mapActions, mapState} from 'vuex';

export default {
  data() {
    return {
      goods: {},
      product: {},
      currentSku: {}, // 当前学习计划
      active: 0, // 判断nav当前active
      timer: null,
      flag: true, // 点击取消滚动监听
      backToTopShow: false, // 是否显示回到顶部
      componentsInfo: {}, // 组件数据
      isLoading: true,
      show_review: this.$store.state.goods.show_review,
      show_course_review: this.$store.state.goods.show_course_review,
      show_classroom_review: this.$store.state.goods.show_classroom_review,
      bindItemBankList: [],
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
    Certificate,
    EnterLearning,
    Vip,
    IconSearch,
    GoodsItemBank
  },
  computed: {
    ...mapState(['vipSwitch']),
    ...mapState({
      goodsSetting: state => state.goodsSettings
    }),
    summary() {
      if (!this.goods.summary) return this.$t('goods.noIntrodution');
      return this.goods.summary;
    },
  },
  methods: {
    ...mapActions('course', ['getCourse', 'getCourseLessons']),
    async getGoodsCourse() {
      try {
        this.goods = await Api.getGoodsCourse({
          query: {
            id: this.$route.params.id
          },
          params: {
            preview: 1
          }
        });
        if (this.$route.query.targetId) {
          this.changeSku(this.$route.query.targetId);
        } else if (this.goods.product.target.defaultCourseId) {
          this.changeSku(this.goods.product.target.defaultCourseId);
        } else {
          this.changeSku(this.goods.product.target.id);
        }
        this.isLoading = false;
        document.documentElement.scrollTop = 0;
        const message = {
          title: this.goods.title,
          link: window.location.href.split('#')[0] + '#' + this.$route.path,
          imgUrl: this.goods.images.small,
          desc: this.goods.summary
        };
        this.share(message);
      } catch (err) {
        Toast.fail(err.message);
      }
      await this.getGoodsCourseComponents();
    },
    goSearch() {
      this.$router.push({ path: '/search', query: { id: this.goods.product.targetId } });
    },
    goItemBank() {
      this.$router.push({ path: '/goods/itemBank', query: { bindId: this.currentSku.targetId, bindType: this.goods.product.targetType } });
    },
    share(message) {
      let desc = ''
      if (message.desc) {
        desc = message.desc.replace(/<\/?.+?>/g, '')
      }
      const shareMessage = {
        title: message.title || '',
        link: message.link,
        imgUrl: message.imgUrl,
        desc: desc || this.$t('goods.findAGoodContent')
      };
      initShare({ ...shareMessage });
    },
    async getGoodsCourseComponents() {
      this.componentsInfo = await Api.getGoodsCourseComponents({
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
      });
    },
    changeSku(targetId) {
      for (const key in this.goods.specs) {
        this.$set(this.goods.specs[key], 'active', false);
        if (targetId == this.goods.specs[key].targetId) {
          this.$set(this.goods.specs[key], 'active', true);
          this.currentSku = this.goods.specs[key];
        }
      }
      if (this.goods.product.targetType === 'course') {
        this.getCourseLessons({
          courseId: targetId,
        }).then(res => {});
        this.getCourse({
          courseId: targetId,
        }).then(res => {});
      }
      this.goods.hasExtension = true;
    },
    onActive(value, eleId) {
      clearTimeout(this.timer);
      this.timer = null;
      this.flag = false;
      this.active = value;
      const eleTop = document.getElementById(eleId).offsetTop;
      const navHeight = document.getElementById('goods-info__nav').offsetHeight;
      document.documentElement.scrollTop = document.body.scrollTop =
        eleTop - navHeight;
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
    updateFavorite(value) {
      this.goods.isFavorite = value;
    },
    async init() {
      await this.getGoodsCourse();
      await this.getBindItemBank();
    },
    async getBindItemBank() {
      this.bindItemBankList = await Api.getBindItemBank({
        params: {
          bindType: this.goods.product.targetType,
          bindId: this.currentSku.targetId,
        }
      })
    }
  },
  created() {
    const targetId = this.$route.query.targetId;
    const type = this.$route.query.type;
    const hasCertificate = this.$route.query.hasCertificate;
    const lastLearnTaskId = this.$route.query.lastLearnTaskId;
    const lastLearnTaskType = this.$route.query.lastLearnTaskType;
    if (type === 'course_list') {
      Api.meCourseMember({
        query: {
          id: targetId,
        },
      })
        .then(res => {
          if (res.id) {
            this.$router.replace({
              path: `/course/${targetId}`,
              query: {
                hasCertificate,
                lastLearnTaskId,
                lastLearnTaskType,
              },
            });
          } else {
            this.init();
          }
        })
        .catch(() => {
          this.init();
        });
    } else if (type === 'classroom_list') {
      Api.meClassroomMember({
        query: {
          id: targetId,
        },
      })
        .then(res => {
          if (res.id) {
            this.$router.replace({
              path: `/classroom/${targetId}`,
              query: {
                hasCertificate,
              },
            });
          } else {
            this.init();
          }
        })
        .catch(() => {
          this.init();
        });
    } else {
      this.init();
    }
  },
  watch: {
    // 如果路由发生变化，再次执行该方法
    $route() {
      this.isLoading = true;
      this.getGoodsCourse();
    },
  },
  mounted() {
    window.addEventListener('scroll', this.handleScroll);
  },
  destroyed() {
    window.removeEventListener('scroll', this.handleScroll);
  },
};
</script>
