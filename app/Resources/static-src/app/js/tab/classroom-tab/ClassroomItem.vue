<template>
  <div class="my-course-item cd-mb16 clearfix">

    <a class="my-course-item__link relative" :href="'/classroom/'+classItem.id">
      <img :src="classItem.cover.middle" :alt="classItem.courseSetTitle" class="my-course-item__picture">
      <span class="absolute" :class="courseStatus.class">
        {{ courseStatus.text }}
      </span>
    </a>
    <div class="my-course-item__info">
      <div class="my-course-item__title text-overflow">
        <a class="cd-link-major text-16" :href="'/classroom/'+classItem.id">
          {{ classItem.title }}</a>
      </div>
      <div class="my-course-item__classroom"><span>已学了{{classItem.spec.usageDays}}天</span></div>

      <div class="my-course-item__progress cd-mt32 cd-clearfix">
        <span class="my-course-item__progress__text">学习进度</span>
        <div class="cd-progress cd-progress-sm">
          <div class="progress-bar">
            <div class="progress-outer">
              <div class="progress-inner" :style="progressClass"></div>
            </div>
          </div>
          <div class="progress-text">{{ classItem.learningProgressPercent }}%</div>
        </div>
      </div>
    </div>
    <div class="my-course-item__btn">
      <a class="btn cd-btn cd-btn-primary" :href="'/classroom/'+classItem.id">{{ btnContent }}</a>
    </div>

  </div>
</template>
<script>
export default {
  props: {
    classItem: {
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
      
      if (this.classItem.status == 'closed') {
        status = {
          class: 'course-status-expired',
          text: '已关闭'
        }
      }

      return status
    },
    btnContent() {
      if (this.classItem?.status === 'closed' || this.classItem?.learningProgressPercent == 100 || this.tabValue == 'expired') {
        return '查看班级'
      }

      return '继续学习'
    },
    progressClass() {
      return {
        width: `${this.classItem.learningProgressPercent}%`
      }
    }
  }
}
</script>