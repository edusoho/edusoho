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

    <!-- 筛选 -->
    <component :is="currentScreenComponent" :id="targetId" @on-search="onSearch" />

    <!-- 题目 -->
    <template v-for="(question, index) in questionList">
      <component
        :is="currentQuestionComponent(question.questions[0].answer_mode)"
        :key="question.id + index"
        :question="question"
        :order="(pagination.current - 1) * 10 + index + 1"
      />
    </template>

    <div class="text-center mt20" v-if="loading">
      <a-spin />
    </div>

    <empty v-if="!loading && !questionList.length" />

    <a-pagination
      class="text-center mt48"
      :hide-on-single-page="true"
      v-model="pagination.current"
      :total="pagination.total"
      @change="onChange"
    />

    <a-modal
      title="错题练习小提示"
      width="400px"
      :visible="visible"
      @cancel="visible = false"
    >
      <p>系统为你随机筛选最多20题重做看你是否掌握</p>

      <template slot="footer">
        <a-button type="primary" @click="goToWrongExercises">
          随机练习
        </a-button>
      </template>
    </a-modal>

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
import Empty from 'app/vue/views/components/Empty.vue';

export default {
  name: 'WrongQuestionDetail',

  components: {
    CourseScreen,
    ClassroomScreen,
    QuestionBankScreen,
    SingleChoice,
    Choice,
    Judge,
    Fill,
    Empty
  },

  data() {
    return {
      targetType: this.$route.params.target_type,
      targetId: this.$route.params.target_id,
      questionList: [],
      searchParams: this.$route.query,
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
      },
      screenComponents: {
        course: 'CourseScreen',
        classroom: 'ClassroomScreen',
        exercise: 'QuestionBankScreen'
      },
      visible: false
    }
  },

  computed: {
    currentScreenComponent() {
      return this.screenComponents[this.targetType];
    }
  },

  created() {
    this.fetchWrongBookQuestion();
  },

  methods: {
    async fetchWrongBookQuestion() {
      this.loading = true;
      const apiParams = {
        params: {
          targetType: this.targetType,
          offset: (this.pagination.current - 1) * 10,
          limit: 10,
          ...this.searchParams
        },
        query: {
          poolId: this.targetId
        }
      };

      const { paging, data } = await WrongBookQuestionShow.search(apiParams);
      this.pagination.total = Number(paging.total);
      this.loading = false;
      this.questionList = data;
    },

    currentQuestionComponent(answerMode) {
      return this.questionComponents[answerMode];
    },

    // 错题练习
    handleClickWrongExercises() {
      if (localStorage.getItem('first_wrong_exercises')) {
        this.goToWrongExercises();
        return;
      }

      this.visible = true;
      localStorage.setItem('first_wrong_exercises', true);
    },

    goToWrongExercises() {
      this.visible = false;
      // 错题练习
    },

    // 错题搜索
    onSearch(params) {
      if (this.judgeSearchParamsChange(params)) {
        this.resetQuery(params);
        this.searchParams = params;
        this.pagination.current = 1;
        this.fetchWrongBookQuestion();
      }
    },

    judgeSearchParamsChange(params) {
      if (_.size(params) != _.size(this.searchParams)) {
        return true;
      }

      let isChange = false;

      _.forEach(params, (value, key) => {
        if (value != this.searchParams[key]) {
          isChange = true;
        }
      });

      return isChange;
    },

    resetQuery(params) {
      this.$router.push({
        query: params
      });
    },

    // 翻页
    onChange() {
      this.fetchWrongBookQuestion();
    }
  }
}
</script>
