<script setup>
import {ref} from 'vue';
import AntConfigProvider from '../../components/AntConfigProvider.vue';
import ItemBankList from './ItemBankList.vue';
import {InfoCircleOutlined} from '@ant-design/icons-vue';

const course = ref($('#item-bank').data('course'));
const courseSet = ref($('#item-bank').data('courseSet'));
// console.log('course', course.value)
// console.log('courseSet', courseSet.value)

const selectedItemBank = ref([]);

const itemBankListVisible = ref(false);
const showItemBankList = () => {
  itemBankListVisible.value = true;
}
</script>

<template>
  <AntConfigProvider>
    <div class="flex flex-col px-32 pt-20">
      <div class="flex justify-between items-center">
        <div class="text-16 font-medium text-black/[.88]">题库练习管理</div>
        <a-button type="primary" class="flex items-center" @click="showItemBankList">
          <InfoCircleOutlined style="font-size: 16px"/>
          <div class="ml-8 text-14 font-normal">绑定题库</div>
        </a-button>
      </div>
      <div v-if="selectedItemBank.length === 0">
        <a-empty description="暂无已绑定的题库" class="mt-150"/>
      </div>
      <div v-else>

      </div>
    </div>
    <ItemBankList v-if="itemBankListVisible" v-model:itemBankListVisible="itemBankListVisible" :bind-id="courseSet.id" bind-type="course"/>
  </AntConfigProvider>
</template>

<style scoped lang="less">

</style>
