<template>
  <div class="brush-exercise-detail-bank">
    <img :src="cover.large" class="brush-exercise-cover" />
    <van-tabs v-model="active" sticky>
      <van-tab title="课程目录">
        <directory :exerciseId="Number(id)" />
      </van-tab>
      <van-tab title="学员评价">
        <review-list
          ref="review"
          title="学员评价"
          defaul-value="暂无评价"
          type="item_bank_exercise"
        />
      </van-tab>
    </van-tabs>
  </div>
</template>

<script>
import { createNamespacedHelpers } from 'vuex';
import directory from './directory';
import reviewList from './review-list';
const { mapState } = createNamespacedHelpers('ItemBank');
export default {
  components: {
    directory,
    reviewList,
  },
  props: ['details'],
  data() {
    return {
      active: 0,
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
