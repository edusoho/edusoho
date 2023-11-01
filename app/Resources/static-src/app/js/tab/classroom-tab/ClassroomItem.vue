<template>
  <div class="my-course-item cd-mb16 clearfix">

    <a class="my-course-item__link relative" href="/course_set/931">
      <img :src="course.courseSet.cover.middle" :alt="course.courseSetTitle" class="my-course-item__picture">
      <span class="absolute" :class="courseStatus.class">
        {{ courseStatus.text }}
      </span>
    </a>
    <div class="my-course-item__info">
      <div class="my-course-item__title text-overflow">
        <a class="cd-link-major text-16" href="/course_set/931">
          {{ course.courseSetTitle }}</a>
      </div>
      <div class="my-course-item__classroom"><span>已学了0天</span></div>

      <div class="my-course-item__progress cd-mt32 cd-clearfix">
        <span class="my-course-item__progress__text">学习进度</span>
        <div class="cd-progress cd-progress-sm">
          <div class="progress-bar">
            <div class="progress-outer">
              <div class="progress-inner" :style="progressClass"></div>
            </div>
          </div>
          <div class="progress-text">{{ course.progress.percent }}%</div>
        </div>
      </div>
    </div>
    <div class="my-course-item__btn">
      <a class="btn cd-btn cd-btn-primary" href="/my/course/981">{{ btnContent }}</a>
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
    }
  },
  computed: {
    courseStatus() {
      let status = {
        class: '',
        text: ''
      }
      if (this.course.spec.status == 'closed') {
        status = {
          class: 'course-status-expired',
          text: '已关闭'
        }
      } else if (this.course.courseSet.type == 'live') {
        status = {
          class: 'course-status-live',
          text: '直播'
        }
      }

      return status
    },
    btnContent() {
      if (this.course.spec.status === 'closed') {
        return '查看课程'
      }

      return '继续学习'
    },
    progressClass() {
      return {
        width: `${this.course.progress.percent}%`
      }
    }
  }
}
</script>