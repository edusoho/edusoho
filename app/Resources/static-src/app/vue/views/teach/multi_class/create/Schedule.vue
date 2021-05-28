<template>
  <div>
    排课只涉及直播课时，其他类型课时设置，请点击-<a>更多课时设置</a>
    <div class="clearfix">
      <a-space size="large">
        <a-button type="primary" @click="showCreateLiveModal">
          <a-icon type="plus" />
          添加直播课时
        </a-button>
        <a-button type="primary">
          <a-icon type="plus" />
          章/节
        </a-button>
      </a-space>
      <a-button class="pull-right">
        批量排课
      </a-button>
    </div>

    <lesson-directory :lesson-directory="lessonDirectory" @change-lesson-directory="changeLessonDirectory" />
    <create-live-modal :visible="createLiveModalVisible" @handle-cancel="hideCreateLiveModal" />
  </div>
</template>

<script>
import LessonDirectory  from './LessonDirectory.vue';
import CreateLiveModal from './CreateLiveModal.vue';
import { Course } from 'common/vue/service';

export default {
  name: 'Schedule',

  components: {
    LessonDirectory,
    CreateLiveModal
  },

  props: {
    courseId: {
      type: [Number, String],
      required: true,
      default: 0
    }
  },

  data() {
    return {
      lessonDirectory: [],
      createLiveModalVisible: true
    }
  },

  watch: {
    courseId(newValue, oldValue) {
      this.fetchCourseLesson(newValue);
    }
  },

  created() {
    this.fetchCourseLesson();
  },

  methods: {
    fetchCourseLesson() {
      if (!this.courseId) return;
      Course.getCourseLesson(this.courseId, { format: 1 }).then(res => {
        this.lessonDirectory = res;
      });
    },

    showCreateLiveModal() {
      this.createLiveModalVisible = true;
    },

    hideCreateLiveModal() {
      this.createLiveModalVisible = false;
    },

    changeLessonDirectory(sortInfos) {
      Course.courseSort(this.courseId, { sortInfos }).then(res => {
        this.fetchCourseLesson();
      });
    }
  }
}
</script>
