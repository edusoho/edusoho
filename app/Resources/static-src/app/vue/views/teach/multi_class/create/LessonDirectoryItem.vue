<template>
  <div :class="`lesson-directory-${lesson.type} clearfix`">
    <div class="title pull-left">{{ lesson.title }}</div>
    <div class="start-time pull-left">2021/08/23 10:12:00</div>
    <div class="duration pull-left">60min</div>
    <div class="actions pull-left">
      <a-space size="large">
        <a-icon type="edit" style="color: #46c37b;" @click="handleEditClick" />
        <a-icon type="delete" style="color: #fe4040;" @click="handleDeleteClick" />
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
      required: true,
      default() {
        return {}
      }
    }
  },

  methods: {
    handleEditClick() {
      console.log('click edit');
    },

    handleDeleteClick() {
      console.log('click delete');
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
    display: none;
    width: 100px;
  }

  &:hover {
    .actions {
      display: block;
    }
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
