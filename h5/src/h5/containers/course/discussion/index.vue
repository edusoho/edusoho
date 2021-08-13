<template>
  <div class="question">
    <van-list
      v-model="loading"
      :finished="finished"
      finished-text="没有更多了"
      @load="onLoad"
    >
      <list-item
        v-for="item in list"
        :key="item.id"
        :item="item"
        @click.native="handleClickViewDetail"
      />
    </van-list>


    <div class="question-btn">
      <van-button
        type="primary"
        block
        @click="handleClickInitiateDiscussion"
      >发起问答</van-button>
    </div>
  </div>
</template>

<script>
import _ from 'lodash';
import Api from '@/api';
import ListItem from './components/ListItem.vue';

export default {
  name: 'Question',

  components: {
    ListItem
  },

  data() {
    return {
      list: [],
      loading: false,
      finished: false,
      paging: {
        offset: 0,
        limit: 20
      }
    }
  },

  created() {
    console.log('fsadf');
  },

  methods: {
    onLoad() {
      const { offset, limit } = this.paging;
      Api.getCoursesThreads({
        query: {
          courseId: 5231
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

        if (this.list.length >= total) {
          this.finished = true;
        }
      });
    },

    handleClickViewDetail() {
      console.log('vuiew');
    },

    handleClickInitiateDiscussion() {

    }
  }
}
</script>

<style lang="scss" scoped>
.question {
  padding-bottom: vw(80);

  &-btn {
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
