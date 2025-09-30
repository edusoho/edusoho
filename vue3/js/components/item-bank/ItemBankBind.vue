<script setup>
import {onBeforeMount, ref} from 'vue';
import Api from '../../../api';
import AntConfigProvider from '../AntConfigProvider.vue';
import {open} from '../../common';
import {t} from './vue-lang';

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

onBeforeMount(async () => {
  await getBindItemBankExercise();
})
</script>

<template>
  <AntConfigProvider>
    <div v-if="bindItemBankList.length === 0" class="flex justify-center py-20 text-[#c1c1c1] text-14 font-normal">{{ t('label.noQuestionBank') }}</div>
    <div v-else class="px-4 md:px-16 pt-15 max-h-800 overflow-y-auto">
      <div v-for="(item, index) in bindItemBankList" class="flex flex-col justify-between md:px-24 md:py-16 border border-[#E5E6EB] border-none md:border-solid rounded-6 md:my-16">
        <div class="flex w-full">
          <div class="flex w-full">
            <img :src="item.itemBankExercise.cover.middle" class="h-76 md:h-90 rounded-5 mr-16" draggable="false" alt="">
            <div class="flex flex-col justify-between">
              <a-tooltip placement="top" :title="item.itemBankExercise.title">
                <div class="text-16 font-medium text-[#37393D] max-w-150 md:max-w-260 truncate hover:text-[#18AD3B] cursor-pointer" @click="open(`/item_bank_exercise/${item.itemBankExercise.id}?bindId=${props.bindId}&bindType=${props.bindType}`)">{{ item.itemBankExercise.title }}</div>
              </a-tooltip>
              <div class="flex flex-col md:flex-row">
                <div class="text-12 text-[#919399] font-normal mr-16 mt-4"><span class="text-[#37393D] mr-2">{{ item.chapterExerciseNum }}</span>{{ t('title.chapterExercises') }}</div>
                <div class="text-12 text-[#919399] font-normal mt-4"><span class="text-[#37393D] mr-2">{{ item.assessmentNum }}</span>{{ t('title.testPaperPractice') }}</div>
              </div>
            </div>
          </div>
          <div class="hidden md:flex md:items-center">
            <a-button type="primary" ghost @click="open(`/item_bank_exercise/${item.itemBankExercise.id}?bindId=${props.bindId}&bindType=${props.bindType}`)">{{ t('btn.check') }}</a-button>
          </div>
        </div>
        <div v-if="bindItemBankList.length  > index + 1" class="border border-t-0 border-[#E5E6EB] border-solid w-full block md:hidden my-16 md:my-0"></div>
      </div>
    </div>
  </AntConfigProvider>
</template>
