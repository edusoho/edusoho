<script setup>
import AntConfigProvider from '../../../components/AntConfigProvider.vue';
import {onBeforeMount, reactive, ref} from 'vue';
import Api from '../../../../api';
import {open} from '../../../common';

const bindItemBankList = ref([]);
const pagination = reactive({
  current: 1,
  total: 0,
  pageSize: 10,
});

async function fetchMyBindItemBank(params) {
  const {data, paging} = await Api.itemBank.getMyBindItemBank(params);
  pagination.total = Number(paging.total);
  pagination.pageSize = Number(paging.total);
  bindItemBankList.value = data;
}

function getTableTotal(total) {
  return `共 ${total} 项`
}

async function handleTableChange(paging) {
  pagination.current = paging.current === 0 ? 1 : paging.current;
  pagination.total = paging.total;
  pagination.pageSize = paging.pageSize;
  const params = {
    limit: paging.pageSize,
    offset: (paging.current - 1) * paging.pageSize,
  };
  await fetchMyBindItemBank(params);
}

async function handlePaginationChange(page, pageSize) {
  pagination.current = page;
  pagination.pageSize = pageSize;
  await handleTableChange(pagination);
}

onBeforeMount(async() => {
  const params = {
    limit: pagination.pageSize,
    offset: (pagination.current - 1) * pagination.pageSize,
  };
  await fetchMyBindItemBank(params);
})
</script>

<template>
  <AntConfigProvider>
    <div class="flex flex-col w-full border border-[#E5E6EB] border-solid bg-white rounded-4">
      <div class="flex flex-col px-24 pt-24 space-y-20 mb-20">
        <div class="text-18 text-[#1E2226] font-medium">我的题库</div>
        <div v-if="bindItemBankList.length === 0">
          <a-empty description="暂无题库"/>
        </div>
        <div v-else v-for="item in bindItemBankList" class="flex flex-col md:flex-row md:justify-between border border-[#E5E6EB] border-solid rounded-6 px-24 py-16">
          <div class="flex flex-col md:flex-row mb-16 md:mb-0">
            <div class="relative md:mr-16">
              <img :src="item.itemBankExercise.cover.middle" class="w-full md:w-176 rounded-6" draggable="false" alt="">
              <div v-if="item.itemBankExercise.status === 'closed'" class="text-12 text-white font-medium px-8 py-2 bg-[#F53F3F] rounded-tl-5 rounded-br-5 leading-20 absolute top-0 left-0">已关闭</div>
            </div>
            <div class="flex flex-col justify-between">
              <div class="flex flex-col">
                <div class="text-16 font-medium text-[#37393D] mb-8 max-w-320 truncate mt-16 md:mt-0">{{ item.itemBankExercise.title }}</div>
                <div class="flex mb-24 md:mb-0">
                  <div class="text-14 text-[#919399] font-normal mr-20">答题率：<span class="text-[#37393D]">{{ `${item.completionRate}%` }}</span></div>
                  <div class="text-14 text-[#919399] font-normal">掌握率：<span class="text-[#37393D]">{{ `${item.masteryRate}%` }}</span></div>
                </div>
              </div>
              <a-tooltip placement="top">
                <template #title>{{ item.bindTitle }}</template>
                <div v-if="item.bindTitle" class="flex text-14 font-normal">
                  <div class="text-[#919399] max-w-320 truncate">{{ item.bindTitle }}<span class="text-[#5E6166] ml-8">赠送的题库</span></div>
                </div>
              </a-tooltip>
            </div>
          </div>
          <div class="flex items-center">
            <a-button class="w-full md:w-fit" type="primary" @click="open(`/item_bank_exercise/${item.itemBankExercise.id}`)">去学习</a-button>
          </div>
        </div>
      </div>
      <div class="flex flex-row-reverse px-40 pt-24 pb-32 border border-[#E5E6EB] border-solid border-x-0 border-b-0">
        <a-pagination
          :show-total="total => getTableTotal(total)"
          v-model="pagination.current"
          :total="pagination.total"
          show-size-changer
          @change="handlePaginationChange"
          :disabled="pagination.total === 0"
        />
      </div>
    </div>
  </AntConfigProvider>
</template>
