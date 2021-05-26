<template>
  <div :class="`lesson-directory-${className} clearfix`">
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
    },

    className: {
      type: String,
      required: true,
      default: ''
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
