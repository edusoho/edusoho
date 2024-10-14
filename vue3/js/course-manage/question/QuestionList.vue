<script setup>
import {ref} from 'vue';

const course = ref($('#question-list').data('course'));
const courseSet = ref($('#question-list').data('courseSet'));
console.log('course', course.value)
console.log('courseSet', courseSet.value)
</script>

<template>
  <div class="text-red-600 text-30">题库测试页面</div>
</template>

<style scoped lang="less">

</style>
