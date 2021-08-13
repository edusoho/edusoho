<template>
  <div class="discussion-detail">
    <h3>f </h3>

    <div class="reply">
      <van-field
        ref="replyInput"
        v-model="value"
        placeholder="回复..."
        @keyup.enter="handleClickEnter"
      />
    </div>
  </div>
</template>

<script>
export default {
  name: 'DiscussionDetail',

  data() {
    return {
      value: ''
    }
  },

  methods: {
    handleClickGoToList() {
      this.$emit('change-current-component', { component: 'List' });
    },

    handleClickEnter() {
      // alert(this.value)
    }
  }
}
</script>

<style lang="scss" scoped>
.discussion-detail {

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
