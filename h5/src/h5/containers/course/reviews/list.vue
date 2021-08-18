<template>
  <div class="reviews-list">
    <van-list
      v-model="loading"
      :finished="finished"
      @load="onLoad"
    >
      <review-item v-for="item in list" :key="item.id" />
    </van-list>

    <empty
      v-if="!list.length && finished"
      text="暂无评价"
    />

    <div class="create-btn">
      <van-button
        type="primary"
        block
        @click="handleClickCreateDiscussion"
      >
        写评价
      </van-button>
    </div>
  </div>
</template>

<script>
import _ from 'lodash';
import Api from '@/api';
import ReviewItem from './components/ReviewItem.vue';
import Empty from '&/components/e-empty/e-empty.vue';

export default {
  name: 'ReviewsCreate',

  components: {
    ReviewItem,
    Empty
  },

  data() {
    return {
      list: [],
      loading: false,
      finished: false,
      paging: {
        offset: 0,
        limit: 10
      },
      courseId: this.$route.params.id
    }
  },

  methods: {
    onLoad() {
      const { offset, limit } = this.paging;
      Api.getCoursesReviews({
        query: {
          courseId: this.courseId
        },
        params: {
          limit: limit,
          offset: offset
        }
      }).then(res => {
        const { data, paging: { total } } = res;

        _.assign(this, {
          list: _.concat(this.list, data),
          loading: false
        });

        this.paging.offset++;

        if (_.size(this.list) >= total) {
          this.finished = true;
        }
      });
    },

    handleClickCreateDiscussion() {
      this.$emit('change-current-component', { component: 'Create' });
    }
  }
}
</script>

<style lang="scss" scoped>
.reviews-list {
  padding-bottom: vw(80);

  .create-btn {
    position: fixed;
    bottom: vw(16);
    left: 50%;
    transform: translateX(-50%);
    width: vw(340);

    .van-button {
      box-shadow: 0px 2px 6px 0px rgba(64, 143, 251, 0.5);
      border-radius: 8px;
      font-size: vw(16);
    }
  }

  .van-list {
    margin-top: 0;
  }

  .e-empty {
    margin-top: vw(50);
  }
}
</style>
