<template>
  <div class="wrong-question-detail">
    查看错题

    <item />
  </div>
</template>

<script>
import Item from './Item.vue';

export default {
  name: 'WrongQuestionDetail',

  components: {
    Item
  }
}
</script>

<style lang="less" scoped>
.wrong-question-detail {
  padding: 24px 16px;
  background-color: #fff;
}
</style>
