<template>
  <div>
    <a-menu v-model="current" mode="horizontal">
      <a-menu-item key="course">
        <router-link :to="{ name: 'CourseWrongQuestion' }">
          课程错题
          <template v-if="wrongBooks.course">
            <span v-if="wrongBooks.course.sum_wrong_num<=999">
              ({{ wrongBooks.course.sum_wrong_num }})
            </span>
            <span v-else>
              (999+)
            </span>
          </template>
        </router-link>
      </a-menu-item>
      <a-menu-item key="classroom">
        <router-link :to="{ name: 'ClassroomWrongQuestion' }">
          班级错题
          <template v-if="wrongBooks.classroom">
            <span v-if="wrongBooks.classroom.sum_wrong_num<=999">
              ({{ wrongBooks.classroom.sum_wrong_num }})
            </span>
            <span v-else>
              (999+)
            </span>
          </template>
        </router-link>
      </a-menu-item>
      <a-menu-item key="question-bank">
        <router-link :to="{ name: 'QuestionBankWrongQuestion' }">
          题库练习错题
          <template v-if="wrongBooks.exercise">
            <span v-if="wrongBooks.exercise.sum_wrong_num<=999">
              ({{ wrongBooks.exercise.sum_wrong_num }})
            </span>
            <span v-else>
              (999+)
            </span>
          </template>
        </router-link>
      </a-menu-item>
    </a-menu>

    <keep-alive>
      <router-view></router-view>
    </keep-alive>
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

    this.fetchWrongBooks();
  },

  methods: {
    async fetchWrongBooks() {
      const result = await Me.getWrongBooks();
      this.wrongBooks = result;
    }
  }
};
</script>
