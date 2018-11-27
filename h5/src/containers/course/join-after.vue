<template>
  <div class="join-after">
    <detail-head :courseSet="details.courseSet"></detail-head>

    <van-tabs v-model="active" :class="tabsClass">
      <van-tab v-for="item in tabs" :title="item" :key="item"></van-tab>
    </van-tabs>

     <!-- 课程目录 -->
    <div class="join-after__content">
      <div v-if="active == 1">
        <div class="progress-bar">
          <div class="progress-bar__content">
            <div class="progress-bar__rate" :style="{'width': progress}"></div>
          </div>
          <div class="progress-bar__text">{{ progress }}</div>
        </div>

        <directory
          :hiddeTitle=true
          class="join-after-dirctory"
          :tryLookable="details.tryLookable"></directory>
      </div>

      <div v-if="active == 0">
        <!-- 课程计划 -->
        <detail-plan></detail-plan>

        <div class="segmentation"></div>
        <!-- 课程介绍 -->
        <e-panel title="课程介绍">
          <div v-html="summary"></div>
        </e-panel>
        <div class="segmentation"></div>

        <!-- 教师介绍 -->
        <teacher
          class="teacher"
          :teacherInfo="details.teachers"></teacher>
      </div>

      <!-- 学员评价 -->
      <div v-if="active == 2">
        <review-list ref="review" :classId="details.courseSet.id" :reviews="details.reviews" title="学员评价" defaulValue="暂无评价" type="classroom"></review-list>
      </div>
    </div>
  </div>
</template>
<script>
import reviewList from '@/containers/classroom/review-list';
import Directory from './detail/directory';
import DetailHead from './detail/head';
import DetailPlan from './detail/plan';
import Teacher from './detail/teacher';
import { mapState } from 'vuex';

export default {
  props: ['details'],
  data() {
    return {
      headBottom: 0,
      active: 1,
      scrollFlag: false,
      tabs: ['班级介绍', '课程目录', '学员评价'],
      tabsClass: '',
    }
  },
  computed: {
    ...mapState('course', {
      selectedPlanId: state => state.selectedPlanId,
    }),
    progress () {
      if(!Number(this.details.publishedTaskNum)) return '0%';

      return parseInt(this.details.progress.percent)+'%';
    },
    summary () {
      return  this.details.summary || this.details.courseSet.summary;
    }
  },
  watch: {
    selectedPlanId: (val, oldVal) => {
      val !== oldVal && (this.active = 0)
      console.log(this.active, 'active')
    }
  },
  components: {
    Directory,
    DetailHead,
    DetailPlan,
    Teacher,
    reviewList
  },
}
</script>
