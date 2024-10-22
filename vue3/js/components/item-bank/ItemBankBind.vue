<script setup>
import {onBeforeMount, ref} from 'vue';
import Api from '../../../api';
import AntConfigProvider from '../AntConfigProvider.vue';

const props = defineProps({
  bindType: {required: true},
  bindId: {required: true},
})

const bindItemBankList = ref([]);
async function getBindItemBankExercise() {
  const params = {
    bindType: props.bindType,
    bindId: props.bindId,
  }
  bindItemBankList.value = await Api.itemBank.getBindItemBankExercise(params);
}

function toItemBankExercisePage(exerciseId) {
  window.location.href = `/item_bank_exercise/${exerciseId}`
}

onBeforeMount(async () => {
  await getBindItemBankExercise();
})
</script>

<template>
  <AntConfigProvider>
    <div v-if="bindItemBankList.length === 0" class="flex justify-center py-20 text-[#c1c1c1] text-14 font-normal">暂无绑定题库</div>
    <div v-else class="px-16 pt-15 max-h-800 overflow-y-auto">
      <div v-for="item in bindItemBankList" class="flex justify-between px-24 py-16 border border-[#E5E6EB] border-solid rounded-6 mb-16">
        <div class="flex">
          <img src="../../../img/course-manage/item-bank/list-state-bg.jpg" class="h-90 rounded-5 mr-16" draggable="false" alt="">
          <div class="flex flex-col justify-between mx-12">
            <a-tooltip placement="top" :title="item.itemBankExercise.title">
              <div class="text-16 font-medium text-[#37393D] max-w-320 truncate hover:text-[#18AD3B] cursor-pointer w-fit" @click="toItemBankExercisePage(item.itemBankExercise.id)">{{ item.itemBankExercise.title }}</div>
            </a-tooltip>
            <div class="flex">
              <div class="text-12 text-[#919399] font-normal mr-16"><span class="text-[#37393D] mr-2">{{ item.chapterExerciseNum }}</span>章节练习</div>
              <div class="text-12 text-[#919399] font-normal"><span class="text-[#37393D] mr-2">{{ item.assessmentNum }}</span>试卷练习</div>
            </div>
          </div>
        </div>
        <div class="flex items-center">
          <a-button type="primary" ghost @click="toItemBankExercisePage(item.itemBankExercise.id)">查看</a-button>
        </div>
      </div>
    </div>
  </AntConfigProvider>
</template>

<style scoped lang="less">

</style>
