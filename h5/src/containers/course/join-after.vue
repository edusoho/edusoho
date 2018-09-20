<template>
  <div class="join-after">
    <detail-head
      :courseSet="details.courseSet"></detail-head>

    <van-tabs
      v-model="active"
      class="after-tabs"
      @click="onTabClick">
      <van-tab v-for="item in tabs"
        :title="item" :key="item"></van-tab>
    </van-tabs>

     <!-- 课程目录 -->
    <div class="join-after__content">
      <template v-if="!active">
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
      </template>

      <template v-else>
        <!-- 课程计划 -->
        <detail-plan></detail-plan>

        <div class="segmentation"></div>
        <!-- 课程介绍 -->
        <e-panel title="课程介绍">
          <div v-html="details.courseSet.summary"></div>
        </e-panel>
        <div class="segmentation"></div>

        <!-- 教师介绍 -->
        <teacher
          class="teacher"
          :teacherInfo="details.teachers"></teacher>
      </template>
    </div>
  </div>
</template>
<script>
import Directory from './detail/directory';
import DetailHead from './detail/head';
import DetailPlan from './detail/plan';
import Teacher from './detail/teacher';
import { mapState } from 'vuex';

export default {
  props: ['details'],
  data() {
    return {
      active: 0,
      tabs: ['课程目录', '课程简介'],
    }
  },
  computed: {
    progress () {
      if(!Number(this.details.publishedTaskNum)) return '0%';

      return parseInt(this.details.progress.percent)+'%';
    },
    ...mapState('course', {
      selectedPlanId: state => state.selectedPlanId,
    })
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
    Teacher
  },
  methods: {
    onTabClick(){
      console.log('click')
    }
  }
}
</script>
