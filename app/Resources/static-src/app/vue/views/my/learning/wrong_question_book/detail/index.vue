<template>
  <a-page-header
    :ghost="false"
  >
    <template slot="title">
      title
    </template>

    <template slot="extra">
      <a-button type="primary">
        错题练习
      </a-button>
    </template>

    <course-screen @on-search="onSearch" />
    <classroom-screen @on-search="onSearch" />
    <question-bank-screen @on-search="onSearch" />

    <student-view />

  </a-page-header>
</template>

<script>
import Item from './Item.vue';
import CourseScreen from './screen/Course.vue';
import ClassroomScreen from './screen/Classroom.vue';
import QuestionBankScreen from './screen/QuestionBank.vue';
import StudentView from '../question/StudentView.vue';

export default {
  name: 'WrongQuestionDetail',

  components: {
    Item,
    CourseScreen,
    ClassroomScreen,
    QuestionBankScreen,
    StudentView
  },

  data() {
    return {
      form: this.$form.createForm(this, { name: 'wrong_question_search' })
    }
  },

  methods: {
    onSearch(values) {
      console.log(values);
    }
  }
}
</script>

<style lang="less" scoped>
.wrong-question-detail {
  padding: 24px 16px;
  background-color: #fff;
}
</style>
