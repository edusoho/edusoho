<template>
  <div :class="`lesson-directory-${lesson.type} clearfix`">
    <!-- <template v-if="lesson.type === 'chapter'">
      <a-col :span="12">{{ lesson.title }}</a-col>
    </template>

    <template v-if="lesson.type === 'unit'">
      <a-col :span="10" :offset="2">{{ lesson.title }}</a-col>
    </template>

    <template v-if="lesson.type === 'lesson'">
      <a-col :span="8" :offset="4">{{ lesson.title }}</a-col>
    </template> -->
    <div class="title pull-left">{{ lesson.title }}</div>

    <div class="start-time pull-left">2021/08/23 10:12:00</div>
    <div class="duration pull-left">60min</div>
    <div class="actions pull-left">编辑, 删除</div>
  </div>
</template>

<script>
export default {
  name: 'LessonDirectoryItem',

  props: {
    lesson: {
      type: Object,
      required: true,
      default() {
        return {}
      }
    }
  }
}
</script>

<style lang="less">
.lesson-directory-chapter,
.lesson-directory-unit,
.lesson-directory-lesson {
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
    width: 100px;
  }
}

.lesson-directory-chapter {
  .title {
    width: 364px;
  }
}

.lesson-directory-unit {
  .title {
    width: 346px;
  }
}

.lesson-directory-lesson {
  .title {
    width: 328px;
  }
}
</style>
