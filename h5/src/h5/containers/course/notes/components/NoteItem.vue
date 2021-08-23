<template>
  <div class="note-item">
    <div class="note-item__top">
      <div class="note-info">
        <img class="avatar" :src="item.user.avatar.small" alt="">
        <span>{{ item.user.nickname }} {{ item.createdTime | formatCourseTime }}</span>
        <span class="tag text-overflow">{{ item.task.title }}</span>
      </div>
      <div class="note-like" :class="{ like: isLike }" @click.stop="handleClickLike">
        <i class="iconfont icon-like"></i>
        {{ item.likeNum }}
      </div>
    </div>
    <div class="note-item__content" v-html="content" />
  </div>
</template>

<script>
import _ from 'lodash';

export default {
  name: 'NoteItem',

  props: {
    item: {
      type: Object,
      required: true
    }
  },

  computed: {
    content() {
      return this.item.content.replace(/<img .*?>/g, '');
    },

    isLike() {
      return !!_.size(this.item.like);
    }
  },

  methods: {
    handleClickLike() {
      this.$emit('handle-like', { noteId: this.item.id, status: this.isLike });
    }
  }
}
</script>

<style lang="scss" scoped>
.note-item {
  padding: vw(16);
  font-size: vw(14);
  color: #666;
  line-height: vw(20);
  border-bottom: 1px solid #f5f5f5;

  &__top {
    display: flex;
    justify-content: space-between;
    align-items: center;

    .note-info {
      display: flex;
      align-items: center;

      .avatar {
        margin-right: vw(4);
        width: vw(24);
        height: vw(24);
        border-radius: 50%;
      }

      .tag {
        margin-left: vw(4);
        max-width: vw(120);
        color: $primary-color;
      }
    }

    .note-like {
      color: #999;
    }

    .like {
      color: $primary-color;
    }
  }

  &__content {
    margin-top: vw(10);
    @include text-overflow(2);
  }
}
</style>
