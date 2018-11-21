<template>
  <div class="course-detail classroom-detail">
    <div class="join-after">
      <detail-head ref="head" :cover="details.cover"></detail-head>
      <van-tabs v-model="active" :class="tabsClass">
        <van-tab v-for="item in tabs" :title="item" :key="item"></van-tab>
      </van-tabs>

      <!-- 班级介绍 -->
      <div v-if="active == 0" style="margin-top: 44px;">
        <detail-plan :details="planDetails" :joinStatus="details.joinStatus"></detail-plan>
        <div class="segmentation"></div>

        <e-panel title="班级介绍" ref="about" class="about">
          <div v-html="details.summary"></div>
        </e-panel>
        <div class="segmentation"></div>

        <!-- 教师介绍 -->
        <teacher
          class="teacher" title="教师介绍"
          :teacherInfo="details.teachers"></teacher>
        <div class="segmentation"></div>

        <teacher
          class="teacher" title="班主任" :teacherInfo="details.headTeacher ? [details.headTeacher] : []"></teacher>
      </div>

      <!-- 班级课程 -->
      <div v-if="active == 1" style="margin-top: 44px;">
        <course-set-list ref="course" :courseSets="details.courses" title="班级课程" defaulValue="暂无课程" :disableMask="true"></course-set-list>
      </div>

      <!-- 学员评价 -->
      <div v-if="active == 2" style="margin-top: 44px;">
        <review-list ref="review" :classId="details.classId" :reviews="details.reviews" title="学员评价" defaulValue="暂无评价"></review-list>
      </div>
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
  const TAB_HEIGHT = 44;

  export default {
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
        headBottom: 0,
        active: 0,
        scrollFlag: false,
        tabs: ['班级介绍', '课程列表', '学员评价'],
        tabsClass: '',
      }
    },
    mounted() {
      window.addEventListener('scroll', this.handleScroll);
    },
    methods: {
      handleScroll() {
        if (this.scrollFlag) {
          return;
        }
        this.scrollFlag = true;
        const refs = this.$refs;

        // 滚动节流
        setTimeout(() => {
          this.headBottom = refs['head'].$el.getBoundingClientRect().bottom
          this.scrollFlag = false;
          this.tabsClass = this.headBottom <= 0 ? 'van-tabs--fixed' : '';
        }, 400)
      },
    },
  }
</script>
