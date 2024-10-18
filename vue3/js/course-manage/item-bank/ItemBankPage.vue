<script setup>
import {computed, ref} from 'vue';
import AntConfigProvider from '../../components/AntConfigProvider.vue';
import ItemBankList from './ItemBankList.vue';
import {InfoCircleOutlined} from '@ant-design/icons-vue';

const course = ref($('#item-bank').data('course'));
const courseSet = ref($('#item-bank').data('courseSet'));

const bindItemBankExerciseList = ref([]);
const bindItemBankExerciseNum = computed(() => {
  return bindItemBankExerciseList.value.length;
})

const itemBankListVisible = ref(false);
const showItemBankList = () => {
  if (bindItemBankExerciseNum.value >= 100) {

  } else {
    itemBankListVisible.value = true;
  }
}
</script>

<template>
  <AntConfigProvider>
    <div class="flex flex-col px-32 pt-20">
      <div class="flex justify-between items-center">
        <div class="text-16 font-medium text-black/[.88]">题库练习管理</div>
        <a-tooltip placement="topLeft">
          <template #title>
            <div class="w-216">绑定后课程学员自动加入题库练习，学员在课程内的学习有效期和学习权限同步影响其在题库练习内的有效期和答题权限。</div>
          </template>
          <a-button type="primary" class="flex items-center" @click="showItemBankList">
            <InfoCircleOutlined style="font-size: 16px"/>
            <div class="ml-8 text-14 font-normal">绑定题库</div>
          </a-button>
        </a-tooltip>
      </div>
      <div v-if="bindItemBankExerciseList.length === 0">
        <a-empty description="暂无已绑定的题库" class="mt-150"/>
      </div>
      <div v-else>

      </div>
    </div>
    <ItemBankList v-if="itemBankListVisible" v-model:itemBankListVisible="itemBankListVisible" :bind-id="courseSet.id" bind-type="course" :bind-item-bank-exercise-num="bindItemBankExerciseNum"/>
  </AntConfigProvider>
</template>

<style scoped lang="less">

</style>
