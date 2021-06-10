<template>
  <div class="text-overflow">
    <span :title="className">{{ className }}</span>
    <br>
    <a-tag v-if="record.tasks.status != 'published'" style="margin-top: 4px;">未发布</a-tag>
  </div>
</template>

<script>
export default {
  name: 'ClassName',

  props: {
    record: {
      type: Object,
      required: true,
      default() {
        return {}
      }
    }
  },

  computed: {
    className() {
      const { chapterTitle, unitTitle, title, tasks, tasks: { number } } = this.record;
      let className = '';

      if (tasks.mode === 'lesson') {
        className = `${number}. ${title}`;
      } else {
        let taskNum = number.split('-');
        className = `${number}.${taskNum[1]-1} [任务]${tasks.title}`;
      }

      if (unitTitle) {
        className = `${unitTitle} ${className}`;
      }

      if (chapterTitle) {
        if (unitTitle) {
          className = `${chapterTitle} - ${className}`;
        } else {
          className = `${chapterTitle} ${className}`;
        }
      }

      return className;
    }
  }
}
</script>
