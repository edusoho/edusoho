<template>
  <div id="app" class="wrong-question-detail">
    <div class="clearfix mb12">
      <h3 class="wrong-question-detail-title pull-left text-overflow mb0">
        {{ title | formatHtml }}
      </h3>

      <a-button class="pull-right" type="primary" :disabled="pagination.total <= 0" @click="handleClickWrongExercises">
        错题练习
      </a-button>
    </div>

    <!-- 筛选 -->
    <component :is="currentScreenComponent" :id="targetId" @on-search="onSearch" @set-title="setTitle" />

    <!-- 题目 -->
    <template v-for="(question, index) in questionList">
      <component
        :is="currentQuestionComponent(question.questions[0].answer_mode)"
        :key="question.id + index"
        :question="question"
        :order="(pagination.current - 1) * 20 + index + 1"
      />
    </template>

    <div class="text-center mt20" style="height: 200px;" v-if="loading">
      <a-spin style="padding-top: 100px;" />
    </div>

    <empty v-if="!loading && !questionList.length" />

    <a-pagination
      class="text-center mt48"
      :hide-on-single-page="true"
      v-model="pagination.current"
      :total="pagination.total"
      :page-size="pagination.pageSize"
      @change="onChange"
    />

    <a-modal
      title="错题练习小提示"
      width="400px"
      :visible="visible"
      @cancel="visible = false"
    >
      <p>已为你随机筛选最多20题</p>

      <template slot="footer">
        <a-button type="primary" @click="goToWrongExercises">
          随机练习
        </a-button>
      </template>
    </a-modal>

  </div>
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
      title: '',
      targetType: this.$route.params.target_type,
      targetId: this.$route.params.target_id,
      questionList: [],
      searchParams: this.$route.query,
      loading: false,
      pagination: {
        current: 1,
        pageSize: 20,
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
      this.questionList = [];
      this.loading = true;
      const apiParams = {
        params: {
          targetType: this.targetType,
          offset: (this.pagination.current - 1) * 20,
          limit: 20,
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
      window.location.href = window.location.origin + `/wrong_question_book/pool/${this.$route.params.target_id}/practise`+ this.makeQuery({targetType:this.targetType, ...this.searchParams});
    },
    makeQuery(queryObject) {
      const query = Object.entries(queryObject)
        .reduce((result, entry) => {
          result.push(entry.join('='))
          return result
        }, []).join('&')
      return `?${query}`
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

    setTitle(title) {
      this.title = title;
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

<style lang="less" scoped>
.wrong-question-detail {
  position: relative;
  padding: 16px 24px;
  box-sizing: border-box;
  margin: 0;
  color: #333;
  background-color: #fff;

  .wrong-question-detail-title {
    max-width: 50%;
    color: rgba(0, 0, 0, .85);
    font-weight: 600;
    font-size: 20px;
    line-height: 32px;
  }
}
</style>
