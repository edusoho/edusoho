<template>
  <div class="e-openCourse-class">
    <div class="column-class-left">
      <img v-lazy="course.middlePicture">
      <div class="column-class-left__live">
        <!-- <div>
          <span v-show="course.courseSet.type === 'live'">直播</span>
        </div> -->
        <div v-if="course.studentNum">
          <i class="iconfont icon-renqi"/>
          {{ course.studentNum }}
        </div>
      </div>
    </div>
    <div class="column-class-right">
      <div class="column-class-right__top text-overflow">
        {{ course.title }}
      </div>
      <div class="column-class-right__live  text-overflow" >
        12:00 | 正在直播 </div>
    </div>
  </div>
</template>

<script>

export default {
  props: {
    course: {
      type: Object,
      default: () => {}
    },
  }
}
</script>
