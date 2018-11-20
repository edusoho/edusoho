<template>
  <div class="course-detail classroom-detail">
    <div class="join-after">
      <detail-head :cover="details.cover"></detail-head>
      <van-tabs v-model="active" class="after-tabs">
        <van-tab v-for="item in tabs" :title="item" :key="item"></van-tab>
      </van-tabs>

      <!-- 班级介绍 -->
      <template v-if="active == 0">
        <template>
          <detail-plan :details="planDetails"></detail-plan>
          <div class="segmentation"></div>
        </template>

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
      </template>

      <!-- 班级课程 -->
      <template v-if="active == 1">
        <course-set-list ref="course" :courseSets="[...details.courses,...details.courses,...details.courses]" title="班级课程" defaulValue="暂无课程" :disableMask="true"></course-set-list>
      </template>

      <!-- 学员评价 -->
      <template v-if="active == 2">
        <review-list ref="review" :classId="details.classId" :reviews="details.reviews" title="学员评价" defaulValue="暂无评价"></review-list>
      </template>
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

  export default {
    name: 'join-after',
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
      }
    },
    computed: {
    },
    methods: {
    },
  }
</script>
