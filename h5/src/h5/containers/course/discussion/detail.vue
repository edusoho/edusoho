<template>
  <div class="discussion-detail">
    <div class="detail-header">
      <van-icon
        name="cross"
        size="18px"
        color="#000"
        @click="handleClickGoToList"
      />
      <h3 class="detail-header__title">话题详情</h3>
      <span class="detail-header__btn">回复</span>
    </div>

    <div class="discussion-body">
      <div class="discussion-body__info">
        <img class="avatar" :src="discussion.user.avatar.small">
        <div class="info-right">
          <span class="info-nickname">{{ discussion.user.nickname }}发起</span>
          <span class="info-time">{{ $moment(discussion.createdTime).format('HH:mm') }}</span>
        </div>
      </div>
      <div class="discussion-body__title">{{ discussion.title }}</div>
      <div class="discussion-body__content" v-html="discussion.content" />
    </div>

    <van-list
      v-model="loading"
      :finished="finished"
      finished-text="没有更多了"
      @load="fetchCourseThreadPost"
    >
      <reply-item v-for="item in replyList" :key="item.id" :item="item" />
    </van-list>

    <div class="reply">
      <van-field
        ref="replyInput"
        v-model="content"
        placeholder="回复..."
        @keyup.enter="handleClickEnter"
      />
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import _ from 'lodash';
import ReplyItem from './components/ReplyItem.vue'

export default {
  name: 'DiscussionDetail',

  components: {
    ReplyItem
  },

  props: {
    discussion: {
      type: Object,
      required: true
    }
  },

  data() {
    return {
      content: '',
      courseId: this.$route.params.id,
      loading: false,
      finished: false,
      paging: {
        offset: 0,
        limit: 10
      },
      replyList: []
    }
  },

  methods: {
    async fetchCourseThreadPost() {
      const { offset, limit } = this.paging;
      const { data, paging: { total} } = await Api.getCoursesThreadPost({
        query: {
          courseId: this.courseId,
          threadId: this.discussion.id
        },
        params: {
          limit: limit,
          offset: offset
        }
      });
      _.assign(this, {
        replyList: _.concat(this.replyList, data),
        loading: false
      });

      this.paging.offset++;

      if (_.size(this.replyList) >= total) {
        this.finished = true;
      }
    },

    handleClickGoToList() {
      this.$emit('change-current-component', { component: 'List' });
    },

    async handleClickEnter() {
      const content = _.trim(this.content);

      if (!content) return;

      const result = await Api.createCoursesThreadPost({
        query: {
          courseId: this.courseId,
          threadId: this.discussion.id
        },
        data: {
          content
        }
      });

      this.replyList.unshift(result);
      this.content = '';
    }
  }
}
</script>

<style lang="scss" scoped>
.discussion-detail {
  padding-bottom: vw(60);

  .detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: vw(16);

    &__title {
      margin: 0;
      font-size: vw(16);
      font-weight: 500;
      color: #333;
      line-height: vw(24);
    }

    &__btn {
      visibility: hidden;
    }
  }


  .discussion-body {
    padding: vw(2) vw(16) vw(16);
    border-bottom: vw(8) solid #f5f5f5;

    &__info {
      display: flex;

      .avatar {
        margin-right: vw(8);
        width: vw(42);
        height: vw(42);
        border-radius: 50%;
      }

      .info-right {
        display: flex;
        flex-direction: column;
        justify-content: space-around;

        .info-nickname {
          font-size: vw(14);
          color: #666;
          line-height: vw(20);
        }

        .info-time {
          font-size: vw(12);
          color: #999;
          line-height: vw(16);
        }
      }
    }

    &__title {
      margin-top: vw(8);
      font-size: vw(16);
      font-weight: 500;
      color: #333;
      line-height: vw(24);
    }

    &__content {
      margin-top: vw(8);
      font-size: vw(12);
      color: #666;
      line-height: vw(16);

      /deep/ img {
        max-width: 100%;
      }
    }
  }

  .reply {
    position: fixed;
    bottom: vw(8);
    left: 50%;
    transform: translateX(-50%);
    width: vw(340);

    .van-cell {
      background: #f5f5f5;
      border-radius: 24px;
    }
  }

  .van-list {
    margin-top: 0;
  }
}
</style>
