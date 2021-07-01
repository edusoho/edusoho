<template>
  <div class="wrong-question-detail">
    <van-swipe
      ref="swipe"
      :height="height"
      :duration="100"
      :loop="false"
      :show-indicators="false"
      @change="onChange"
    >
      <van-swipe-item>
        <question />
      </van-swipe-item>
      <van-swipe-item>
        <question />
      </van-swipe-item>
      <van-swipe-item>
        <question />
      </van-swipe-item>
      <van-swipe-item>
        <question />
      </van-swipe-item>
    </van-swipe>

    <div class="question-foot">
      错题练习
    </div>
  </div>
</template>

<script>
import { mapMutations } from 'vuex';
import * as types from '@/store/mutation-types';
import Api from '@/api';
import Question from '../components/Question.vue';

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
        },
      }).then(res => {
        this.questionList = res.data;
      });
    },

    onChange(index) {
      console.log(index);
    },
  },
};
</script>
