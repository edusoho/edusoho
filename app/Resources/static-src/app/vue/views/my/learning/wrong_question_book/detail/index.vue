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
      title="错题练习"
      width="446px"
      ok-text="开始答题"
      cancel-text="取消"
      :visible="visible"
      @cancel="popoverVisible = false"
      @ok="goToWrongExercises"
    >
      <div>
        展示数量：
        <a-radio-group v-model:value="modeValue" name="radioGroup">
          <a-radio class="font-normal" value="A">{{ Math.min(wrongNumCount,20) }}题</a-radio>
          <a-radio class="font-normal" value="B">自定义</a-radio>
        </a-radio-group>
        <div v-show="modeValue === 'B'">
          <div>
            <a-input class="item-num" v-model:value="itemNum" type="number" v-on:input="changeInput" v-on:blur="blurInput" />题
          </div>
          <div class="item-num-tip">
            可输入范围：1≤题目数量≤单个错题本全部错题
          </div>
        </div>
      </div>

    </a-modal>

  </div>
</template>

<script>
import _ from 'lodash';
import { WrongBookQuestionShow, WrongBook_pool } from 'common/vue/service';
import CourseScreen from './screen/Course.vue';
import ClassroomScreen from './screen/Classroom.vue';
import QuestionBankScreen from './screen/QuestionBank.vue';
import SingleChoice from './components/SingleChoice.vue';
import Choice from './components/Choice.vue';
import Judge from './components/Judge.vue';
import Fill from './components/Fill.vue';
import Empty from 'app/vue/views/components/Empty.vue';
import { message } from 'ant-design-vue';
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
      visible: false,
      modeValue:'A',
      itemNum:1,
      wrongNumCount:null,
    }
  },

  computed: {
    currentScreenComponent() {
      return this.screenComponents[this.targetType];
    }
  },

  created() {
    this.fetchWrongBookQuestion();
    this.getWrongBook_pool()
  },

  methods: {
    changeInput() {
      if (!/^[0-9]+$/.test(this.itemNum)) {
        this.itemNum = this.itemNum.replace(/[^\d]/g,'');
      }
      if (this.itemNum > this.wrongNumCount) {
        this.itemNum = this.wrongNumCount;
      }
      if (this.itemNum < 0) {
        this.itemNum = 1
      }
    },
    blurInput() {
      if (this.itemNum === '') {
        this.itemNum = 1
      }
    },
    async getWrongBook_pool() {
      const apiParams = {
        params: {
          targetType: this.targetType
        },
        query: {
          poolId: this.targetId
        }
      };
      const res = await WrongBook_pool.get(apiParams)
      this.wrongNumCount = res.wrongNumCount
    },
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
      this.wrongNumCount = Number(paging.total)
    },

    currentQuestionComponent(answerMode) {
      return this.questionComponents[answerMode];
    },

    // 错题练习
    handleClickWrongExercises() {
      this.visible = true;
    },

    goToWrongExercises() {
      if(this.itemNum == 0 && this.modeValue === 'B') return message.warning('答题数不能为0');

      this.visible = false;
      // 错题练习
      if (this.modeValue === 'B') {
        window.location.href = window.location.origin + `/wrong_question_book/pool/${this.$route.params.target_id}/practise`+ this.makeQuery({targetType:this.targetType, itemNum:this.itemNum, ...this.searchParams});
      } else {
        window.location.href = window.location.origin + `/wrong_question_book/pool/${this.$route.params.target_id}/practise`+ this.makeQuery({targetType:this.targetType, ...this.searchParams});
      }
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
.item-num {
  margin: 8px 8px 8px 74px;
  width: 102px;
}
.item-num-tip {
  margin-left: 74px;
  color: #919399;
}
</style>
