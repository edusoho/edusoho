<template>
  <div>
    <head-top />

    <situation />
  </div>
</template>

<script>
import HeadTop from './QuestionElement/HeadTop.vue';
import Situation from './QuestionElement/Situation.vue';

export default {
  components: {
    HeadTop,
    Situation,
  },
};
</script>
