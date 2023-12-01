<template>
  <div class="my-course-item cd-mb16 clearfix">

    <a class="my-course-item__link relative" :href="'/my/course/' + course.id">
      <img :src="course.courseSet.cover.middle" :alt="course.courseSetTitle" class="my-course-item__picture">
      <span class="absolute" :class="courseStatus.class">
        {{ courseStatus.text }}
      </span>
    </a>
    <div class="my-course-item__info">
      <div class="my-course-item__title text-overflow">
        <a class="cd-link-major text-16" :href="'/my/course/' + course.id">
          {{ course.courseSetTitle }}
        </a>
      </div>
      <div class="mt8 text-overflow">
        <a class="cd-link-assist" href="/classroom/883">{{ course.title }}</a>
      </div>

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
      <a class="btn cd-btn cd-btn-primary" :href="'/my/course/' + course.id">{{ btnContent }}</a>
    </div>

  </div>
</template>
<script>
export default {
  props: {
    course: {
      type: Object,
      default: {}
    },
    tabValue: {
      type: String,
      default: ''
    }
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
      if (this.course?.courseSet?.status == 'closed') {
        status = {
          class: 'course-status-expired',
          text: '已关闭'
        }
      } else if (this.course?.courseSet?.type == 'live') {
        status = {
          class: 'course-status-live',
          text: '直播'
        }
      }

      return status
    },
    btnContent() {
      if (this.course?.courseSet?.status === 'closed' || this.tabValue == 'expired' || this.course?.progress?.percent == 100) {
        return '查看课程'
      }

      return '继续学习'
    },
    progressClass() {
      return {
        width: `${this.course?.progress?.percent}%`
      }
    }
  }
}
</script>