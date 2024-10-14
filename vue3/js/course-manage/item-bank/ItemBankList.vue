<script setup>
import {ref} from 'vue';
import AntConfigProvider from '../../components/AntConfigProvider.vue';
import {InfoCircleOutlined} from '@ant-design/icons-vue';

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
        <a-button type="primary" class="flex items-center">
          <InfoCircleOutlined style="font-size: 16px"/>
          <div class="ml-8 text-14 font-normal">绑定题库</div>
        </a-button>
      </div>
      <div>

      </div>
    </div>
  </AntConfigProvider>
</template>

<style scoped lang="less">

</style>
