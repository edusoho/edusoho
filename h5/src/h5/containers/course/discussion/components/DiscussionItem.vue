<template>
  <div class="discussion-item">
    <h3 class="discussion-title text-overflow">{{ item.title }}</h3>
    <p class="discussion-desc" v-html="content" />
    <div class="discussion-details">
      <div class="discussion-details__left">
        <img class="user-avatar" :src="item.user.avatar.small">
        <span>{{ item.user.nickname }} {{ item.createdTime | formatCourseTime }}</span>
      </div>
      <div class="discussion-details__right">
        <i class="iconfont icon-review"></i>
        {{ item.postNum }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DiscussionItem',

  props: {
    item: {
      type: Object,
      required: true
    }
  },

  computed: {
    content() {
      return this.item.content.replace(/<img .*?>/g, '');
    }
  }
}
</script>

<style lang="scss" scoped>
.discussion-item {
  padding: vw(16);
  border-bottom: 1px solid #f5f5f5;

  .discussion-title {
    margin: 0;
    font-size: vw(16);
    font-weight: 500;
    color: #333;
    line-height: vw(24);
  }

  .discussion-desc {
    margin-top: vw(8);
    font-size: vw(12);
    color: #666;
    line-height: vw(16);
    @include text-overflow(2);
    word-break: break-word;
  }

  .discussion-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: vw(8);
    font-size: vw(14);
    line-height: vw(20);

    &__left {
      display: flex;
      align-items: center;
      color: #666;

      .user-avatar {
        margin-right: vw(4);
        width: vw(24);
        height: vw(24);
        border-radius: 50%;
      }
    }

    &__right {
      color: #999;

      i {
        vertical-align: middle;
      }
    }
  }
}
</style>
