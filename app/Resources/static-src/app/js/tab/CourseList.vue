<template>
  <div>
    <div class="my-course-item cd-mb16 clearfix">

      <a class="my-course-item__link relative" href="/course_set/931">
        <img :src="course.courseSet.cover.middle"
          :alt="course.courseSetTitle" class="my-course-item__picture">
        <span class="absolute" :class="">{{courseStatus}}
        </span> </a>
      <div class="my-course-item__info">
        <div class="my-course-item__title text-overflow">

          <a class="cd-link-major" href="/course_set/931">{{course.courseSetTitle}}</a>
        </div>


        <div class="my-course-item__progress cd-mt32 cd-clearfix">
          <span class="my-course-item__progress__text">学习进度</span>
          <div class="cd-progress cd-progress-sm">
            <div class="progress-bar">
              <div class="progress-outer">
                <div class="progress-inner" :style="`width: ${course.progress.percent}%;`"></div>
              </div>
            </div>
            <div class="progress-text">{{ course.progress.percent }}%</div>
          </div>
        </div>
      </div>
      <div class="my-course-item__btn">
        <a class="btn cd-btn cd-btn-primary" href="/my/course/981">继续学习</a>
      </div>

    </div>
  </div>
</template>
<script>
export default {
  props: {
    course: Object
  },
  data() {
    return {
      progress: 97,
    }
  },
  computed:{
    courseStatus() {
      if (this.course.spec.status === 'closed') {
        return {
          class: 'course-status-expired',
          text: '已关闭'
        }
      }

      if(this.course.courseSet.type === 'live') {
        return  {
          class: 'course-status-live',
          text: '直播'
        }
      }
      
      return
    },
    btnContent() {
      
    }
  }
}
</script>