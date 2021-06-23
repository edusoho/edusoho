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

    <question-item />

  </a-page-header>
</template>

<script>
import { WrongBookQuestionShow } from 'common/vue/service';
import CourseScreen from './screen/Course.vue';
import ClassroomScreen from './screen/Classroom.vue';
import QuestionBankScreen from './screen/QuestionBank.vue';
import QuestionItem from './QuestionItem.vue';

export default {
  name: 'WrongQuestionDetail',

  components: {
    CourseScreen,
    ClassroomScreen,
    QuestionBankScreen,
    QuestionItem
  },

  data() {
    return {
      targetType: this.$route.params.target_type,
      targetId: this.$route.params.target_id
    }
  },

  created() {
    this.fetchWrongBookQuestion();
  },

  methods: {
    async fetchWrongBookQuestion() {
      const params = {
        id: this.targetId,
      };
      const res = await WrongBookQuestionShow.search(params);
      console.log(res);
    },

    // 错题练习
    handleClickWrongExercises() {

    },

    // 错题搜索
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
