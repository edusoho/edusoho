<template>
  <div class="my-course-item cd-mb16 clearfix">

    <div class="my-course-item-course">
      <div class="my-course-item-course__info">
        <a class="my-course-item-course__link" :href="'/my/course/' + course.id">
          <img :src="course.courseSet.cover.middle" :alt="course.courseSetTitle" class="my-course-item__picture">
        </a>
        <div class="my-course-item-course__detail">
          <div class="my-course-item-course__title">
            <div class="my-course-item__title text-overflow">
              <a class="cd-link-major text-16" :href="'/my/course/' + course.id">
                {{ course.courseSetTitle }}
              </a>
            </div>
            <div class="text-overflow">
              <span class="cd-link-assist">{{ course.title }}</span>
            </div>
          </div>

          <div class="my-course-item-course__learn">
            <div class="my-course-item__progress my-course-item-course__progress">
              <span style="flex-shrink: 0">{{ 'tab.course_tab.course_item.learning_progress'|trans }}</span>
              <div class="cd-progress cd-progress-sm">
                <div class="progress-bar">
                  <div class="progress-outer">
                    <div class="progress-inner" :style="progressClass"></div>
                  </div>
                </div>
              </div>
              <div>{{ course.progress.percent }}%</div>
            </div>
            <div class="course-learn-history" v-if="course.lastLearnTask">
              <span class="history-icon"></span>
              <a :href="`/course/${course.id}/task/${course.lastLearnTask.id}/show`">
                {{ 'tab.course_tab.course_item.last_time_learned'|trans }}{{ `${course.lastLearnTask.number} : ${course.lastLearnTask.title}` }}
              </a>
            </div>
            <div v-else class="my-course-item-course__empty"></div>
          </div>
        </div>
      </div>
      <div class="my-course-item-course__btn">
        <a class="btn cd-btn cd-btn-primary" :href="'/my/course/' + course.id">{{ btnContent }}</a>
      </div>
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
    return {}
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
          text: Translator.trans('tab.course_tab.course_item.closed')

        }
      } else if (this.course?.courseSet?.type == 'live') {
        status = {
          class: 'course-status-live',
          text: Translator.trans('tab.course_tab.course_item.live_streaming')
        }
      }

      return status
    },
    btnContent() {
      if (this.course?.courseSet?.status === 'closed' || this.tabValue == 'expired' || this.course?.progress?.percent == 100) {
        return Translator.trans('tab.course_tab.course_item.view_course')
      }

      return Translator.trans('tab.course_tab.course_item.start_studying')
    },
    progressClass() {
      return {
        width: `${this.course?.progress?.percent}%`
      }
    }
  }
}
</script>
