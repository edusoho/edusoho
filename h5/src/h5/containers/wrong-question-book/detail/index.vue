<template>
  <div class="wrong-question-detail">
    <e-loading v-if="isLoading" />
    <van-swipe
      v-if="questionList.length"
      ref="swipe"
      :height="height"
      :duration="100"
      :loop="false"
      :show-indicators="false"
      :lazy-render="true"
      :initial-swipe="currentIndex"
      @change="onChange"
      style="overflow-y: auto;"
    >
      <van-swipe-item
        v-for="(question, index) in questionList"
        :key="question.id + index"
      >
        <question
          :total="pagination.total"
          :order="index + 1"
          :question="question"
        />
      </van-swipe-item>
    </van-swipe>

    <van-empty
      v-else
      style="transform: translateY(50%);"
      description="暂无错题"
    />

    <div v-if="questionList.length" class="paper-swiper">
      <div
        :class="['left-slide__btn', currentIndex == 0 ? 'slide-disabled' : '']"
        @click="prev()"
      >
        <i class="iconfont icon-arrow-left" />
      </div>
      <div
        :class="[
          'right-slide__btn',
          currentIndex == questionList.length - 1 ? 'slide-disabled' : '',
        ]"
        @click="next()"
      >
        <i class="iconfont icon-arrow-right" />
      </div>
    </div>

    <div class="question-search" @click="showSearch">
      <van-icon name="filter-o" />
      筛选
    </div>

    <!-- 筛选组件 -->
    <component
      :is="currentSearchComponent"
      :show="show"
      :pool-id="targetId"
      :exercise-media-type="exerciseMediaType"
      @hidden-search="hiddenSearch"
      @on-search="onSearch"
    />

    <div
      v-if="questionList.length"
      class="question-foot"
      @click="onClickWrongExercise"
    >
      错题练习
    </div>
  </div>
</template>

<script>
import _ from 'lodash';
import { mapMutations } from 'vuex';
import * as types from '@/store/mutation-types';
import Api from '@/api';
import { Dialog } from 'vant';
import Question from './Question/index.vue';
import CourseSearch from './Search/Course.vue';
import ClassroomSearch from './Search/Classroom.vue';
import QuestionBankSearch from './Search/QuestionBank.vue';

const NavBarHeight = 46;
const FootHeight = 48;
const DocHeight = document.documentElement.clientHeight;
const MaxHeight = DocHeight - NavBarHeight - FootHeight;

export default {
  name: 'WrongQuestionBookDetail',

  components: {
    Question,
    // eslint-disable-next-line vue/no-unused-components
    CourseSearch,
    // eslint-disable-next-line vue/no-unused-components
    ClassroomSearch,
    // eslint-disable-next-line vue/no-unused-components
    QuestionBankSearch,
  },

  data() {
    return {
      isLoading: false,
      targetType: this.$route.params.type,
      targetId: this.$route.params.id,
      exerciseMediaType: this.$route.query.type,
      questionList: [],
      pagination: {
        current: 1,
        total: 0,
        pageSize: 20,
      },
      finished: false,
      height: MaxHeight,
      currentIndex: 0,
      searchParams: {},
      show: false,
      searchComponents: {
        course: 'CourseSearch',
        classroom: 'ClassroomSearch',
        exercise: 'QuestionBankSearch',
      },
    };
  },

  computed: {
    currentSearchComponent() {
      return this.searchComponents[this.targetType];
    },
  },

  created() {
    this.setNavbarTitle(this.$route.query.title);
    this.fetchWrongQuestion();
  },

  methods: {
    ...mapMutations({
      setNavbarTitle: types.SET_NAVBAR_TITLE,
    }),

    fetchWrongQuestion() {
      this.isLoading = true;
      const { current, pageSize } = this.pagination;
      Api.getWrongBooksQuestionShow({
        query: {
          poolId: this.targetId,
        },
        params: {
          targetType: this.targetType,
          limit: pageSize,
          offset: (current - 1) * pageSize,
          exerciseMediaType: this.exerciseMediaType,
          ...this.searchParams,
        },
      }).then(res => {
        const { data, paging } = res;
        this.questionList = _.concat(this.questionList, data);
        this.pagination.total = paging.total;
        this.finished = false;
        this.isLoading = false;
        if (_.size(this.questionList) >= paging.total) {
          this.finished = true;
        }
      });
    },

    onChange(index) {
      this.currentIndex = index;
      const maxLength = _.size(this.questionList) - 3;
      if (!this.finished && index >= maxLength) {
        this.pagination.current++;
        this.finished = true;
        this.fetchWrongQuestion();
      }
    },

    prev() {
      if (this.currentIndex == 0) {
        return;
      }
      this.$refs.swipe.swipeTo(this.currentIndex - 1);
    },

    next() {
      if (this.currentIndex == this.questionList.length - 1) {
        return;
      }
      this.$refs.swipe.swipeTo(this.currentIndex + 1);
    },

    showSearch() {
      this.show = true;
    },

    hiddenSearch() {
      this.show = false;
    },

    onSearch(params) {
      this.searchParams = params;
      this.questionList = [];
      this.pagination.current = 1;
      this.currentIndex = 0;
      this.fetchWrongQuestion();
    },

    onClickWrongExercise() {
      if (!localStorage.getItem('first_wrong_exercises')) {
        Dialog.alert({
          message: '已为你随机筛选最多 20 题',
          confirmButtonText: '我知道了',
          confirmButtonColor: '#03c777 !important',
        }).then(() => {
          this.goToStartAnswer();
        });
        localStorage.setItem('first_wrong_exercises', true);
        return;
      }
      this.goToStartAnswer();
    },

    goToStartAnswer() {
      this.$router.push({
        name: 'WrongExercisesDo',
        query: {
          id: this.targetId,
          ...this.searchParams,
        },
      });
    },
  },
};
</script>
