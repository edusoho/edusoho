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

export default {
  name: 'DiscussionDetail',

  props: {
    id: {
      type: String,
      required: true
    }
  },

  data() {
    return {
      content: '',
      courseId: this.$route.params.id
    }
  },

  created() {
    this.fetchCourseThreadPost();
  },

  methods: {
    async fetchCourseThreadPost() {
      const result = await Api.getCoursesThreadPost({
        query: {
          courseId: this.courseId,
          threadId: this.id
        },
        params: {
          limit: 10,
          offset: 0
        }
      });
      console.log(result);
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
          threadId: this.id
        },
        data: {
          content
        }
      });
      console.log(result);
    }
  }
}
</script>

<style lang="scss" scoped>
.discussion-detail {

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
}
</style>
