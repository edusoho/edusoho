<template>
  <div>
    <div v-if="reviews === null">
      <van-loading color="#1989fa" size="24px" vertical style="margin-top: 20px"
        >加载中...</van-loading
      >
    </div>
    <e-panel
      v-else
      :title="title"
      :need-flex="false"
      :defaul-value="defaulValue"
    >
      <moreMask
        v-if="reviews.length"
        :max-height="400"
        @maskLoadMore="loadMore"
      >
        <!-- <moreMask v-if="reviews.length" @maskLoadMore="loadMore" :disabled="diableMask" :forceShow="!diableMask"> -->
        <template v-for="(item, index) in reviews">
          <review :review="item" :is-class="isBank" :key="index" />
        </template>
      </moreMask>
    </e-panel>
  </div>
</template>
<script>
import review from '&/components/e-review';
import moreMask from '@/components/more-mask';
import { mapState } from 'vuex';
export default {
  name: 'ReviewList',
  components: {
    review,
    moreMask,
  },
  props: ['title', 'defaulValue', 'type'],
  data() {
    return {
      maxShowNum: 5,
      type1: 'classroom',
    };
  },
  computed: {
    ...mapState('ItemBank', {
      reviews: state => state.reviews,
      targetId: state => state.ItemBankExercise.id,
    }),
    isBank() {
      return this.type === 'item_bank_exercise';
    },

    // diableMask() {
    //   return this.reviews.length < this.maxShowNum;
    // }
  },
  methods: {
    loadMore() {
      this.$router.push({
        path: `/comment/${this.targetId}`,
        query: {
          type: this.type,
        },
      });
    },
  },
};
</script>
