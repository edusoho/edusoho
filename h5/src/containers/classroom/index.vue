<template>
  <div class="course-detail classroom-detail">
    <e-loading v-if="isLoading"></e-loading>

    <div :class="details.joinStatus ? 'join-after' : 'join-before'">
      <detail-head :cover="details.cover"></detail-head>

      <template v-if="!details.joinStatus">
        <detail-plan :type="type" :details="planDetails"></detail-plan>
        <div class="segmentation"></div>
      </template>

      <van-tabs v-model="active" @click="onTabClick" :class="tabsClass">
        <van-tab v-for="item in tabs" :title="item" :key="item"></van-tab>
      </van-tabs>

      <!-- 班级介绍 -->
      <e-panel title="班级介绍" ref="about" class="about">
        <more-mask :asyncLoaded="details.summary" :disabled="loadMoreAbout" @maskLoadMore="loadMoreAbout = true">
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
        class="teacher" title="班主任" :teacherInfo="[details.headTeacher]"></teacher>
      <div class="segmentation"></div>

      <!-- 班级课程 -->
      <course-set-list ref="course" :courseSets="details.courses" title="班级课程" defaulValue="暂无课程"></course-set-list>
      <div class="segmentation"></div>

      <!-- 学员评价 -->
      <review-list ref="review" :classId="details.classId" :reviews="details.reviews" title="学员评价"></review-list>

      <e-footer v-if="!details.joinStatus" @click.native="handleJoin">{{details.access.code | filterJoinStatus}}</e-footer>
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
  import { mapState } from 'vuex';
  import Api from '@/api';

  const TAB_HEIGHT = 44;

  export default {
    name: 'classroom',
    components: {
      directory,
      detailHead,
      detailPlan,
      teacher,
      courseSetList,
      reviewList,
      moreMask
    },
    data() {
      return {
        type: 'classroom',
        tops: {
          aboutTop: 0,
          courseTop: 0,
          reviewTop: 0,
        },
        active: 0,
        scrollFlag: false,
        tabs: ['班级介绍', '课程列表', '学员评价'],
        tabsClass: '',
        details: {
          isEmpty: true,
          summary: '',
          joinStatus: false,
          courses: [],
          teachers: [],
          assistants: [],
          headTeacher: {},
          access: {
            code: '获取课程失败'
          },
          cover: '',
          reviews: [],
          classId: 0,
        },
        planDetails: {
          title: '',
          service: [],
          price: '0',
          studentNum: 0,
        },
        loadMoreAbout: false,
      }
    },
    computed: {
      ...mapState({
        isLoading: state => state.isLoading
      }),
    },
    created(){
      Api.getClassroomDetail({
        query: { classroomId: 1 }
      }).then(res => {
        this.getDetails(res);
      })
    },
    mounted() {
      window.addEventListener('scroll', this.handleScroll);
    },
    methods: {
      getDetails(res) {
        if (this.type === 'classroom') {
          const isEmpty = Object.keys(res).length === 0;
          const summary = res.about;
          const joinStatus = res.member && !isEmpty;
          const courses = res.courses;
          const price = res.price;
          const teachers = res.teachers;
          const assistants = res.assistants;
          const headTeacher = res.headTeacher;
          const access = res.access;
          const cover = res.cover.large;
          const reviews = res.reviews;
          const classId = res.id;
          const planDetails = {
            title: res.title,
            service: res.service,
            price: res.price,
            studentNum: res.studentNum,
          };

          this.planDetails = planDetails;
          this.details = {
            summary, joinStatus, isEmpty, courses, classId,
            teachers, assistants, headTeacher, access, cover, reviews,
          }
        }
      },
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
    },
  }
</script>
