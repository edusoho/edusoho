<template>
  <div class="reply-item">
    <div class="reply-item__info">
      <img class="avatar" :src="item.user.avatar.small">
      <span>{{ item.user.nickname }} {{ $moment(item.createdTime).format('HH:mm') }}</span>
    </div>
    <div class="reply-item__content" v-html="item.content" />
  </div>
</template>

<script>
export default {
  name: 'ReplyItem',

  props: {
    item: {
      type: Object,
      required: true
    }
  }
}
</script>

<style lang="scss" scoped>
.reply-item {
  padding: vw(16);

  &__info {
    display: flex;
    align-items: center;

    .avatar {
      margin-right: vw(4);
      width: vw(24);
      height: vw(24);
      border-radius: 50%;
    }

    span {
      font-size: vw(14);
      color: #666;
      line-height: vw(20);
    }
  }

  &__content {
    margin-top: vw(8);
    font-size: vw(14);
    color: #666;
    line-height: vw(20);
  }
}
</style>
