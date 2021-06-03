<template>
  <div>
    排课只涉及直播课时，其他类型课时设置，请点击-<a :href="`/course_set/${1}/manage/course/${courseId}/tasks`">更多课时设置</a>
    <div class="clearfix">
      <a-space size="large">
        <a-button type="primary" :disabled="courseId == 0" @click="showCreateLiveModal">
          <a-icon type="plus" />
          添加直播课时
        </a-button>
        <a-dropdown :disabled="courseId == 0" :trigger="['click']">
          <a-button type="primary">
            <a-icon type="plus" />
            章/节
          </a-button>
          <a-menu slot="overlay" @click="showAddChapterOrUnitModal">
            <a-menu-item key="chapter">添加章</a-menu-item>
            <a-menu-item key="unit">添加节</a-menu-item>
          </a-menu>
        </a-dropdown>
      </a-space>
      <a-button v-if="false" class="pull-right">
        批量排课
      </a-button>
    </div>

    <lesson-directory
      :courseId="courseId"
      :lesson-directory="lessonDirectory"
      @change-lesson-directory="changeLessonDirectory"
    />
    <create-live-modal
      :courseId="courseId"
      :visible="createLiveVisible"
      @handle-cancel="hideCreateLiveModal"
      @change-lesson-directory="changeLessonDirectory"
    />
    <add-chapter-or-unit-modal
      :type="addType"
      :courseId="courseId"
      :visible="addChapterOrUnitVisible"
      @handle-cancel="hideAddChapterOrUnitModal"
      @change-lesson-directory="changeLessonDirectory"
    />
  </div>
</template>

<script>
import LessonDirectory  from './LessonDirectory.vue';
import CreateLiveModal from './CreateLiveModal.vue';
import AddChapterOrUnitModal from './AddChapterOrUnitModal.vue';
import { Course } from 'common/vue/service';

export default {
  name: 'Schedule',

  components: {
    LessonDirectory,
    CreateLiveModal,
    AddChapterOrUnitModal
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
      createLiveVisible: false,
      addChapterOrUnitVisible: false,
      addType: ''
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

  mounted() {
    $('#modal').on('hide.bs.modal', () => {
      this.fetchCourseLesson();
    });
  },

  methods: {
    fetchCourseLesson() {
      if (!this.courseId) return;
      Course.getCourseLesson(this.courseId, { format: 1 }).then(res => {
        this.lessonDirectory = res;
      });
    },

    showCreateLiveModal() {
      this.createLiveVisible = true;
    },

    hideCreateLiveModal(visible) {
      this.createLiveVisible = visible;
    },

    changeLessonDirectory(params) {
      const { data = this.lessonDirectory, addData, type } = params;

      if (type === 'update') {
        this.fetchCourseLesson();
        return;
      }

      const sortInfos = [];

      const loop = (sortInfos, data) => {
        _.forEach(data, lesson => {
          const { type, id } = lesson;
          sortInfos.push(`${type}-${id}`);
          if (lesson.children) {
            loop(sortInfos, lesson.children)
          }
        });
      };

      loop(sortInfos, data);

      if (addData) {
        loop(sortInfos, addData);
      }

      Course.courseSort(this.courseId, { sortInfos }).then(res => {
        this.fetchCourseLesson();
      });
    },

    showAddChapterOrUnitModal({ key }) {
      this.addChapterOrUnitVisible = true;
      this.addType = key;
    },

    hideAddChapterOrUnitModal() {
      this.addChapterOrUnitVisible = false;
    }
  }
}
</script>
