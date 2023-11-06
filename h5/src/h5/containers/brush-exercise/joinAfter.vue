<template>
  <div>
  <div v-if="isOpen" class="brush-exercise-detail-bank">
    <img :src="cover.large" class="brush-exercise-cover" />
    <van-tabs v-model="active" sticky>
      <van-tab :title="$t('questionBank.intro')">
        <introduction></introduction>
      </van-tab>
      <van-tab :title="$t('questionBank.catalogue')">
        <directory :exerciseId="Number(id)" />
      </van-tab>
      <van-tab :title="$t('questionBank.comment')" v-if="show_question_bank_review == 1">
        <review-list
          ref="review"
          :title="$t('questionBank.comment')"
          :defaul-value="$t('questionBank.noContent')"
          type="item_bank_exercise"
        />
      </van-tab>
    </van-tabs>
  </div>
  <div class="flex flex-col items-center" v-else>
    <img class="hide-exercise" src="static/images/exercise/hide-exerice.png" />
    <div class="text-text-7 font-medium">当前题库练习已关闭</div>
    <div class="text-12 text-text-6 mt-6">无法继续学习</div>
    <a class="goLearn mt-36">回到学习页</a>
  </div>
</div>
</template>

<script>
import { createNamespacedHelpers } from 'vuex';
import directory from './directory';
import reviewList from './review-list';
import introduction from './introduction';
const { mapState } = createNamespacedHelpers('ItemBank');
export default {
  components: {
    directory,
    reviewList,
    introduction,
  },
  props: ['details'],
  data() {
    return {
      active: 1,
      show_question_bank_review: this.$store.state.goods
        .show_question_bank_review,
      isOpen: true
    };
  },
  computed: {
    ...mapState({
      cover: state => state.ItemBankExercise.cover,
      id: state => state.ItemBankExercise.id,
    }),
  },
  watch: {},
  created() {},
  methods: {},
};
</script>
<style scoped>
.hide-exercise {
  display: flex;
  width: 236px;
  height: 236px;
  justify-content: center;
  align-items: center;
  margin-top: 160px;
}

.goLearn {
  padding: 5px 15px;
  text-align: center;
  border-radius: 18px;
  background: #5DB85D;
  color: #fff;
}
</style>
