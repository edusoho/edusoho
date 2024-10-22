<script setup>
import AntConfigProvider from '../../../components/AntConfigProvider.vue';
import {onBeforeMount, reactive, ref} from 'vue';
import Api from '../../../../api';

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
    <div class="flex flex-col w-full border border-[#E4ECF3] border-solid bg-white">
      <div class="flex flex-col px-24 pt-24 space-y-20">
        <div class="text-18 text-[#1E2226] font-medium">我的题库</div>
        <div v-for="item in bindItemBankList" class="border border-[#E5E6EB] border-solid px-24 py-16">

        </div>
      </div>
      <div>

      </div>
    </div>
  </AntConfigProvider>
</template>
