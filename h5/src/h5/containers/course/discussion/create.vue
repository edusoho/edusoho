<template>
  <div>
    <van-button type="primary" @click="handleClickGoToList">返回</van-button>
  </div>
</template>

<script>
export default {
  name: 'DiscussionDetail',

  methods: {
    handleClickGoToList() {
      this.$emit('change-current-component', { component: 'List' });
    }
  }
}
</script>
