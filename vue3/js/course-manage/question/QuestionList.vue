<script setup>
import {ref} from 'vue';
import AntConfigProvider from '../../components/AntConfigProvider.vue';

const course = ref($('#question-list').data('course'));
const courseSet = ref($('#question-list').data('courseSet'));
console.log('course', course.value)
console.log('courseSet', courseSet.value)
</script>

<template>
  <AntConfigProvider>
    <div class="flex flex-col px-32 pt-20">
      <div class="flex justify-between items-center">
        <div class="text-16 font-medium text-black/[.88]">题库练习管理</div>
        <a-button type="primary">Primary Button</a-button>
      </div>
      <div>

      </div>
    </div>
  </AntConfigProvider>
</template>

<style scoped lang="less">

</style>
