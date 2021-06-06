<template>
  <div :class="`lesson-directory-${className} clearfix`">
    <div class="title pull-left">
      <a-tag v-if="lesson.mode && lesson.type !== 'live'">非直播</a-tag>
      <a-tag v-if="lesson.mode && lesson.status !== 'published'">未发布</a-tag>
      {{ getTitle }}
    </div>
    <div class="start-time pull-left">{{ getStartTime }}</div>
    <div class="duration pull-left">{{ getLength }}</div>
    <div class="actions pull-left">
      <a-space size="large">
        <a-icon
          v-if="lesson.mode"
          type="edit"
          data-toggle="modal"
          data-target="#modal"
          :data-url="`/course/${courseId}/task/${lesson.id}/update`"
          style="color: #46c37b;"
        />
        <a-icon
          v-if="['chapter', 'unit'].includes(lesson.type)"
          type="edit"
          style="color: #46c37b;"
          @click="handleEditorClick"
        />
        <a-icon
          v-if="lesson.type !== 'lesson' && lesson.status !== 'published'"
          type="delete"
          style="color: #fe4040;"
          @click="handleDeleteClick"
        />
      </a-space>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LessonDirectoryItem',

  props: {
    lesson: {
      type: Object,
      required: true
    },

    className: {
      type: String,
      required: true
    },

    courseId: {
      type: [Number, String],
      required: true
    },
  },

  computed: {
    getTitle() {
      const { type, title, number } = this.lesson;
      if (type === 'chapter') return `第${number}章 ${title}`;
      if (type === 'unit') return `第${number}节 ${title}`;
      if (type === 'lesson') return `课时${number} ${title}`;
      return `任务${number} ${title}`;
    },

    getStartTime() {
      const { type, startTime } = this.lesson;
      if (type === 'live') return this.$dateFormat(startTime, 'YYYY/MM/DD HH:mm:ss');
      return '- -';
    },

    getLength() {
      const { type, length } = this.lesson;
      if (type === 'live') return `${length} 分钟`;
      return '- -';
    }
  },

  methods: {
    handleDeleteClick() {
      const { type, id } = this.lesson;
      const that = this;
      this.$confirm({
        title: '删除',
        content: `确认删除?`,
        okText: '确认',
        okType: 'danger',
        cancelText: '取消',
        onOk() {
          that.$emit('event-communication', {
            eventType: ['chapter', 'unit'].includes(type) ? 'deleteChapterUnit' : 'deleteTask',
            id
          });
        }
      });
    },

    handleEditorClick() {
      const { type, id, title } = this.lesson;
      this.$emit('event-communication', {
        eventType: 'renameChapterUnit',
        id,
        type,
        title
      });
    }
  }
}
</script>

<style lang="less">
.lesson-directory-first,
.lesson-directory-second,
.lesson-directory-third,
.lesson-directory-four {
  line-height: 30px;
  height: 30px;
  border-bottom: 1px solid #ebebeb;

  .start-time {
    width: 170px;
  }

  .duration {
    width: 80px;
  }

  .actions {
    display: none;
    width: 100px;
  }

  &:hover {
    .actions {
      display: block;
    }
  }
}

.lesson-directory-first {
  .title {
    width: 364px;
  }
}

.lesson-directory-second {
  .title {
    width: 346px;
  }
}

.lesson-directory-third {
  .title {
    width: 328px;
  }
}

.lesson-directory-four {
  .title {
    width: 310px;
  }
}
</style>
