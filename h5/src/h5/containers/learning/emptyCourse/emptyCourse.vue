<template>
  <div class="empty-course">
    <img class="empty-course__img" src="static/images/courseEmpty.png" alt="" />
    <p class="empty-course__text">{{ emptyText }}</p>
    <div v-if="hasButton" class="empty-course__btn" @click="jumpBack">
      + {{ moreText }}
    </div>
  </div>
</template>

<script>

export default {
  props: {
    hasButton: {
      type: Boolean,
      default: true,
    },
    type: {
      type: String,
      default: 'course',
    },
    text: {
      type: String,
      default: "暂无数据"
    }
  },
  computed:{
    emptyText() {
      return this.text;
    },
    moreText() {
      const type = this.type;
      switch (type) {
        case 'course_list':
          return this.$t('e.moreCourse');
        case 'classroom_list':
          return this.$t('e.moreClass');
        case 'item_bank_exercise':
          return this.$t('e.moreQuestionBanks');
      }
      return '';
    },
  },
  methods: {
    jumpBack() {
      this.$router.push({
        name: 'find',
        query: {
          redirect: 'find',
        },
      });
    },
    getEmptyText() {
      return `暂无${this.type === 'course_list' ? '课程' : '班级'}`;
    },
  },
};
</script>
