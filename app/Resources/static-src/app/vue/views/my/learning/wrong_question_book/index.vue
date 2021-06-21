<template>
  <div>
    <a-menu v-model="current" mode="horizontal">
      <a-menu-item key="course">
        <router-link :to="{ name: 'CourseWrongQuestion' }">
          课程错题
          <template v-if="wrongBooks.course">
            ({{ wrongBooks.course.sum_wrong_num }})
          </template>
        </router-link>
      </a-menu-item>
      <a-menu-item key="classroom">
        <router-link :to="{ name: 'ClassroomWrongQuestion' }">
          班级错题
          <template v-if="wrongBooks.classroom">
            ({{ wrongBooks.classroom.sum_wrong_num }})
          </template>
        </router-link>
      </a-menu-item>
      <a-menu-item key="question-bank">
        <router-link :to="{ name: 'QuestionBankWrongQuestion' }">
          题库错题
          <template v-if="wrongBooks.exercise">
            ({{ wrongBooks.exercise.sum_wrong_num }})
          </template>
        </router-link>
      </a-menu-item>
    </a-menu>

    <router-view />
  </div>
</template>

<script>
import { Me } from 'common/vue/service/index.js';

export default {
  name: 'WrongQuestionBook',

  data() {
    return {
      current: ['course'],
      wrongBooks: {}
    };
  },

  created() {
    this.current = [this.$route.meta.current];

    this.initWrongBooks();
  },

  methods: {
    async initWrongBooks() {
      const result = await Me.getWrongBooks();
      this.wrongBooks = result;
    }
  }
};
</script>
