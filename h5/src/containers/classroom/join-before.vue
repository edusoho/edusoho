<template>
  <div class="course-detail classroom-detail">
    <div class="join-before">
      <detail-head :cover="details.cover"></detail-head>

      <detail-plan :details="planDetails" :joinStatus="details.joinStatus"
        @getLearnExpiry="getLearnExpiry"></detail-plan>
      <div class="segmentation"></div>

      <!-- 优惠活动 -->
      <onsale :unreceivedCoupons="unreceivedCoupons" :miniCoupons="miniCoupons" />
      <div class="segmentation"></div>

      <van-tabs v-model="active" @click="onTabClick" :class="tabsClass">
        <van-tab v-for="item in tabs" :title="item" :key="item"></van-tab>
      </van-tabs>

      <!-- 班级介绍 -->
      <e-panel title="班级介绍" ref="about" class="about">
        <more-mask :disabled="loadMoreAbout" @maskLoadMore="loadMoreAbout = true">
          <div v-html="details.summary"></div>
        </more-mask>
      </e-panel>
      <div class="segmentation"></div>

      <!-- 教师介绍 -->
      <teacher
        class="teacher" title="教师介绍"
        :teacherInfo="details.teachers"></teacher>
      <div class="segmentation"></div>

      <teacher
        class="teacher" title="班主任" :teacherInfo="details.headTeacher ? [details.headTeacher] : []"
        defaulValue="尚未设置班主任"></teacher>
      <div class="segmentation"></div>

      <!-- 班级课程 -->
      <course-set-list ref="course" :courseSets="details.courses" title="班级课程" defaulValue="暂无课程" :disableMask.sync="disableMask"></course-set-list>
      <div class="segmentation"></div>

      <!-- 学员评价 -->
      <review-list ref="review" :targetId="details.classId" :reviews="details.reviews" title="学员评价" type="classroom" defaulValue="暂无评价"></review-list>

      <e-footer :disabled="!accessToJoin" @click.native="handleJoin">
      {{details.access.code | filterJoinStatus('classroom')}}</e-footer>
    </div>

  </div>
</template>

<script>
  import teacher from './teacher';
  import detailHead from './head';
  import reviewList from './review-list';
  import courseSetList from './course-set-list';
  import detailPlan from './plan';
  import directory from '../course/detail/directory';
  import onsale from '../course/detail/onsale'
  import moreMask from '@/components/more-mask';
  import redirectMixin from '@/mixins/saveRedirect';
  import Api from '@/api';

  const TAB_HEIGHT = 44;

  export default {
    mixins: [redirectMixin],
    components: {
      directory,
      detailHead,
      detailPlan,
      teacher,
      courseSetList,
      reviewList,
      moreMask,
      onsale,
    },
    props: ['details', 'planDetails'],
    data() {
      return {
        tops: {
          aboutTop: 0,
          courseTop: 0,
          reviewTop: 0,
        },
        active: 0,
        scrollFlag: false,
        tabs: ['班级介绍', '班级课程', '学员评价'],
        tabsClass: '',
        loadMoreAbout: false,
        disableMask: false,
        learnExpiry: '永久有效',
        unreceivedCoupons: [],
        miniCoupons: [],
      }
    },
    computed: {
      accessToJoin() {
        return this.details.access.code === 'success'
          || this.details.access.code === 'user.not_login';
      },
    },
    mounted() {
      Api.searchCoupon({
        params: {
          targetId: this.details.classId,
          targetType: 'classroom',
        }
      }).then(res => {
        for (var i = 0; i < res.length; i++) {
          if (res[i].unreceivedNum == 0 && !res[i].currentUserCoupon) {
            continue;
          }
          if (res[i].currentUserCoupon && res[i].currentUserCoupon.status === 'used') {
            continue;
          }
          this.unreceivedCoupons.push(res[i]);
        }
        this.miniCoupons = this.unreceivedCoupons.length > 3 ?
          this.unreceivedCoupons.slice(0, 4) : this.unreceivedCoupons
      })
      window.addEventListener('touchmove', this.handleScroll);
      window.addEventListener('scroll', this.handleScroll);
      setTimeout(() => {
        window.scrollTo(0,0);
      }, 100)
    },
    destroyed () {
      window.removeEventListener('touchmove', this.handleScroll);
      window.removeEventListener('scroll', this.handleScroll);
    },
    methods: {
      onTabClick(index, title) {
        const ref = this.$refs[this.transIndex2Tab(index)];
        window.scrollTo(0, ref.$el.offsetTop - TAB_HEIGHT);
      },
      transIndex2Tab(index) {
        const tabs = ['about', 'course', 'review']
        return tabs[index];
      },
      handleScroll() {
        if (this.scrollFlag) {
          return;
        }
        this.scrollFlag = true;
        const refs = this.$refs;
        const tabs = ['about', 'course', 'review'].reverse()

        // 滚动节流
        setTimeout(() => {
          Object.keys(refs).forEach(item => {
            this.tops[`${item}Top`] = refs[item].$el.getBoundingClientRect().top
          })
          this.scrollFlag = false;
          this.tabsClass = this.tops.aboutTop - TAB_HEIGHT <= 0 ? 'van-tabs--fixed' : '';

          for (let index = 0; index < tabs.length; index++) {
            const activeCondition = this.tops[`${tabs[index]}Top`] - TAB_HEIGHT <= 0
            if (!activeCondition) {
              continue;
            }
            this.active = tabs.length - index - 1;
            return;
          }
        }, 400)
      },
      handleJoin() {
        if (!this.accessToJoin) {
          return;
        }
        const details = this.details;
        const planDetails = this.planDetails;
        const canJoinIn = details.access.code === 'success'
          || Number(details.buyable ) === 1 || (+planDetails.price) === 0;

        if (!this.$store.state.token) {
          this.$router.push({
            name: 'login',
            query: {
              redirect: this.redirect
            }
          });
          return;
        }

        if (!canJoinIn) return;

        if (+planDetails.price) {
          this.$router.push({
            name: 'order',
            params: {
              id: details.classId,
            },
            query: {
              expiryScope: this.learnExpiry,
              targetType: 'classroom',
            }
          });
          return;
        }

        Api.joinClass({
          query: {
            classroomId: details.classId
          }
        }).then(res => {
          this.details.joinStatus = res;
        }).catch(err => {
          console.error(err.message);
        });
      },
      getLearnExpiry({val}) {
        this.learnExpiry = val;
      }
    },
  }
</script>
