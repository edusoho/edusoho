<template>
  <a-page-header
    :ghost="false"
  >
    <template slot="title">
      title
    </template>

    <template slot="extra">
      <a-button type="primary" @click="handleClickWrongExercises">
        错题练习
      </a-button>
    </template>

    <course-screen
      v-if="targetType == 'course'"
      @on-search="onSearch"
    />

    <classroom-screen
      v-else-if="targetType == 'classroom'"
      @on-search="onSearch"
    />

    <question-bank-screen
      v-else-if="targetType == 'exercise'"
      @on-search="onSearch"
    />

    <template v-for="question in questionList">
      <component
        :is="currentQuestionComponent(question.question.answer_mode)"
        :key="question.id"
        :question="question"
      />
    </template>

    <div class="text-center mt20" v-if="loading">
      <a-spin />
    </div>

    <a-pagination
      class="text-center mt48"
      :hide-on-single-page="true"
      v-model="pagination.current"
      :total="pagination.total"
      @change="onChange"
    />
  </a-page-header>
</template>

<script>
import _ from 'lodash';
import { WrongBookQuestionShow } from 'common/vue/service';
import CourseScreen from './screen/Course.vue';
import ClassroomScreen from './screen/Classroom.vue';
import QuestionBankScreen from './screen/QuestionBank.vue';
import SingleChoice from './components/SingleChoice.vue';
import Choice from './components/Choice.vue';
import Judge from './components/Judge.vue';
import Fill from './components/Fill.vue';

export default {
  name: 'WrongQuestionDetail',

  components: {
    CourseScreen,
    ClassroomScreen,
    QuestionBankScreen,
    SingleChoice,
    Choice,
    Judge,
    Fill
  },

  data() {
    return {
      targetType: this.$route.params.target_type,
      targetId: this.$route.params.target_id,
      questionList: [],
      loading: false,
      pagination: {
        current: 1
      },
      questionComponents: {
        single_choice: 'SingleChoice',
        choice: 'Choice',
        uncertain_choice: 'Choice',
        true_false: 'Judge',
        text: 'Fill'
      }
    }
  },

  created() {
    this.fetchWrongBookQuestion();
  },

  methods: {
    async fetchWrongBookQuestion() {
      this.loading = true;
      const params = {
        id: this.targetId,
        targetType: this.targetType,
        courseId: 72,
        offset: (this.pagination.current - 1) * 10,
        limit: 10
      };
      const { paging, data } = await WrongBookQuestionShow.search(params);
      this.pagination.total = Number(paging.total);
      this.loading = false;
      this.questionList = data;
    },

    currentQuestionComponent(answerMode) {
      return this.questionComponents[answerMode];
    },

    // 错题练习
    handleClickWrongExercises() {

    },

    // 错题搜索
    onSearch(values) {
      console.log(values);
    },

    // 翻页
    onChange() {
      this.fetchWrongBookQuestion();
    }
  }
}
</script>
