<script setup>
import AntConfigProvider from '../../components/AntConfigProvider.vue';
import {ref, h} from 'vue';
import { PlusCircleOutlined } from '@ant-design/icons-vue';
import { Empty } from 'ant-design-vue';
const simpleImage = Empty.PRESENTED_IMAGE_SIMPLE;

const lessons = ref([]);
</script>
<template>
  <AntConfigProvider>
    <div class="flex flex-col py-24 px-32">
      <div class="flex justify-between items-center">
        <div class="text-16 font-medium text-black text-opacity-88">课时管理</div>
        <div class="flex space-x-20">
          <a-button type="primary" :icon="h(PlusCircleOutlined)">添加回放</a-button>
          <a-button type="primary" :icon="h(PlusCircleOutlined)">添加直播</a-button>
        </div>
      </div>
      <div v-if="lessons.length === 0">
        <a-empty :image="simpleImage" class="mt-140"/>
      </div>
      <div v-else>kjsbdkjasbdjksabdkjsabdaksjbdaskjbd,dsakbdsakhvdsakjhbdsakjndbsdbsamdvasjhdvas</div>
    </div>
  </AntConfigProvider>
</template>

<style scoped lang="less">

</style>
