<template>
  <div>
    <a-menu v-model="current" mode="horizontal">
      <a-menu-item key="course">课程错题</a-menu-item>
      <a-menu-item key="classroom">班级错题</a-menu-item>
      <a-menu-item key="question-bank">题库错题</a-menu-item>
    </a-menu>

    <router-view />
  </div>
</template>

<script>
export default {
  name: 'WrongQuestionBook',

  data() {
    return {
      current: ['course'],
    };
  }
};
</script>

<style lang="less">

</style>
