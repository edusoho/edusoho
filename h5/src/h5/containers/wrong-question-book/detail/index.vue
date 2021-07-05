<template>
  <div class="wrong-question-detail">
    <van-swipe
      ref="swipe"
      :height="height"
      :duration="100"
      :loop="false"
      :show-indicators="false"
      @change="onChange"
      style="overflow-y: auto;"
    >
      <van-swipe-item
        v-for="(question, index) in questionList"
        :key="question.id + index"
      >
        <question
          :total="pagination.total"
          :order="(pagination.current - 1) * 20 + index + 1"
          :question="question"
        />
      </van-swipe-item>
    </van-swipe>

    <div class="question-foot">
      错题练习
    </div>
  </div>
</template>

<script>
import _ from 'lodash';
import { mapMutations } from 'vuex';
import * as types from '@/store/mutation-types';
import Api from '@/api';
import Question from './Question/index.vue';

const NavBarHeight = 46;
const FootHeight = 48;
const DocHeight = document.documentElement.clientHeight;
const MaxHeight = DocHeight - NavBarHeight - FootHeight;

export default {
  name: 'WrongQuestionBookDetail',

  components: {
    Question,
  },

  data() {
    return {
      targetType: this.$route.params.type,
      targetId: this.$route.params.id,
      questionList: [],
      pagination: {
        current: 1,
        total: 0,
      },
      finished: false,
      height: MaxHeight,
    };
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
      Api.getWrongBooksQuestionShow({
        query: {
          poolId: this.targetId,
        },
        params: {
          targetType: this.targetType,
          limit: 20,
          offset: (this.pagination.current - 1) * 20,
        },
      }).then(res => {
        const { data, paging } = res;
        this.questionList = _.concat(this.questionList, data);
        this.pagination.total = paging.total;
        this.finished = false;
        if (_.size(this.questionList) >= paging.total) {
          this.finished = true;
        }
      });
    },

    onChange(index) {
      const maxLength = _.size(this.questionList) - 3;
      if (!this.finished && index >= maxLength) {
        this.pagination.current++;
        this.finished = true;
        this.fetchWrongQuestion();
      }
    },
  },
};
</script>
