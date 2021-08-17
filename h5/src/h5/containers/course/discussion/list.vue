<template>
  <div class="discussion-list">
    <van-list
      v-model="loading"
      :finished="finished"
      finished-text="没有更多了"
      @load="onLoad"
    >
      <discussion-item
        v-for="item in list"
        :key="item.id"
        :item="item"
        @click.native="handleClickViewDetail(item)"
      />
    </van-list>

    <div class="create-btn">
      <van-button
        type="primary"
        block
        @click="handleClickCreateDiscussion"
      >
        发起问答
      </van-button>
    </div>
  </div>
</template>

<script>
import _ from 'lodash';
import Api from '@/api';
import DiscussionItem from './components/DiscussionItem.vue';

export default {
  name: 'DiscussionList',

  components: {
    DiscussionItem
  },

  props: {
    type: {
      type: String,
      required: true
    }
  },

  data() {
    return {
      list: [],
      loading: false,
      finished: false,
      paging: {
        offset: 0,
        limit: 20
      },
      courseId: this.$route.params.id
    }
  },

  methods: {
    onLoad() {
      const { offset, limit } = this.paging;
      Api.getCoursesThreads({
        query: {
          courseId: this.courseId
        },
        params: {
          type: this.type,
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

    handleClickViewDetail(data) {
      this.$emit('change-current-component', { component: 'Detail', data });
    },

    handleClickCreateDiscussion() {
      this.$emit('change-current-component', { component: 'Create' });
    }
  }
}
</script>

<style lang="scss" scoped>
.discussion-list {
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
}
</style>
