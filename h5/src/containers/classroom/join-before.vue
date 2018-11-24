<template>
  <div class="course-detail classroom-detail">
    <div class="join-before">
      <detail-head :cover="details.cover"></detail-head>

      <template>
        <detail-plan :details="planDetails" :joinStatus="details.joinStatus" @getLearnExpiry="getLearnExpiry"></detail-plan>
        <div class="segmentation"></div>
      </template>

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
        class="teacher" title="班主任" :teacherInfo="details.headTeacher ? [details.headTeacher] : []"></teacher>
      <div class="segmentation"></div>

      <!-- 班级课程 -->
      <course-set-list ref="course" :courseSets="details.courses" title="班级课程" defaulValue="暂无课程" :disableMask.sync="disableMask"></course-set-list>
      <div class="segmentation"></div>

      <!-- 学员评价 -->
      <review-list ref="review" :classId="details.classId" :reviews="details.reviews" title="学员评价" type="classroom" defaulValue="暂无评价"></review-list>

      <e-footer @click.native="handleJoin">{{details.access.code | filterJoinStatus('classroom')}}</e-footer>
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
      moreMask
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
        tabs: ['班级介绍', '课程列表', '学员评价'],
        tabsClass: '',
        loadMoreAbout: false,
        disableMask: false,
        learnExpiry: '永久有效',
      }
    },
    mounted() {
      window.addEventListener('touchmove', this.handleScroll);
    },
    destroyed () {
      window.removeEventListener('touchmove', this.handleScroll);
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
              expiry: this.learnExpiry,
              type: 'classroom',
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
      getLearnExpiry(val) {
        this.learnExpiry = val;
      }
    },
  }
</script>
