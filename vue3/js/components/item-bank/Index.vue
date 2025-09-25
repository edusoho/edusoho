<script setup>
import {computed, createVNode, onBeforeMount, ref} from 'vue';
import AntConfigProvider from '../AntConfigProvider.vue';
import ItemBankDrawer from './ItemBankDrawer.vue';
import {ExclamationCircleOutlined, HolderOutlined, InfoCircleOutlined} from '@ant-design/icons-vue';
import {message, Modal} from 'ant-design-vue';
import Api from '../../../api';
import draggable from 'vuedraggable';
import {formatDate, open} from '../../common';
import {t} from './vue-lang';

const props = defineProps({
  bindType: {required: true},
  bindId: {required: true},
})

const bindItemBankExerciseList = ref([]);
const ids = ref([]);
const bindItemBankExerciseNum = computed(() => {
  return bindItemBankExerciseList.value.length;
})

const itemBankListVisible = ref(false);
const showItemBankList = () => {
  if (bindItemBankExerciseNum.value >= 100) {
    message.error(t('message.limit'));
  } else {
    itemBankListVisible.value = true;
  }
}

function integerPart(num) {
  return num.split('.')[0];
}

function decimalPart(num) {
  return num.split('.')[1];
}

const loading = ref(false);
async function getBindItemBankExercise() {
  const params = {
    bindType: props.bindType,
    bindId: props.bindId,
  }
  try {
    loading.value = true;
    bindItemBankExerciseList.value = await Api.itemBank.getBindItemBankExercise(params);
    ids.value = bindItemBankExerciseList.value.map(item => item.id);
  } finally {
    loading.value = false;
  }
}

async function sequenceItemBankExerciseBind() {
  const newIds = bindItemBankExerciseList.value.map(item => item.id);
  if (ids.value.every((value, index) => value === newIds[index])) {
    return;
  }
  const params = {
    ids: newIds,
    bindType: props.bindType,
    bindId: props.bindId,
  }
  await Api.itemBank.sequenceBindItemBankExercise(params);
  await getBindItemBankExercise();
}

async function deleteBindItemBank(id) {
  await Api.itemBank.deleteBindItemBank(id);
  await getBindItemBankExercise();
}

function showDeleteConfirm(id) {
  Modal.confirm({
    title: t('label.deletePracticeQuestions'),
    centered: true,
    icon: createVNode(ExclamationCircleOutlined),
    content: createVNode('div', { style: 'font-size: 14px; color: #5E6166; font-weight: 400; ' }, props.bindType === 'course' ? t('label.deleteFromCourse') : t('label.deleteFromClassroom')),
    async onOk() {
      await deleteBindItemBank(id)
      message.success(t('message.successfullyDelete'));
    },
    onCancel() {
    },
  });
}

onBeforeMount(async () => {
  await getBindItemBankExercise();
})
</script>

<template>
  <AntConfigProvider>
    <div class="flex flex-col px-32 pt-20">
      <div class="flex justify-between items-center mb-20">
        <div class="text-16 font-medium text-black/[.88]">{{ t('label.management') }}</div>
        <a-tooltip placement="topLeft">
          <template #title>
            <div class="w-216">{{ props.bindType === 'course' ? t('tooltip.course') : t('tooltip.classroom') }}</div>
          </template>
          <a-button type="primary" class="flex items-center" @click="showItemBankList">
            <InfoCircleOutlined style="font-size: 16px"/>
            <div class="ml-8 text-14 font-normal">{{ t('btn.bind') }}</div>
          </a-button>
        </a-tooltip>
      </div>
      <a-spin :spinning="loading" :tip="t('label.loading')">
        <div v-if="bindItemBankExerciseList.length === 0">
          <a-empty :description="t('label.noBound')" class="mt-150"/>
        </div>
        <div v-else class="mb-20">
          <draggable
            v-model="bindItemBankExerciseList"
            group="people"
            @end="sequenceItemBankExerciseBind"
            item-key="id">
            <template #item="{element}">
              <div class="flex justify-between px-24 py-16 border border-[#DFE2E6] border-solid rounded-6 mb-16 bg-white cursor-all-scroll">
                <div class="flex space-x-16">
                  <div class="flex items-center">
                    <HolderOutlined class="w-16 text-[#919399]"/>
                  </div>
                  <div class="relative">
                    <img :src="element.itemBankExercise.cover.middle" class="h-90 rounded-5" draggable="false" alt="">
                    <div v-if="element.itemBankExercise.status === 'published'" class="text-12 text-white font-medium px-8 py-2 bg-[#00C261] rounded-tl-5 rounded-br-5 leading-20 absolute top-0 left-0">{{ t('label.havePublished') }}</div>
                    <div v-if="element.itemBankExercise.status === 'closed'" class="text-12 text-white font-medium px-8 py-2 rounded-tl-5 rounded-br-5 leading-20 absolute top-0 left-0 bg-[rgba(0,0,0,0.6)]">{{ t('label.closed') }}</div>
                  </div>
                  <div class="flex flex-col justify-between">
                    <a-tooltip placement="top" :title="element.itemBankExercise.title">
                      <div class="text-16 font-medium text-[#37393D] max-w-250 truncate hover:text-[#18AD3B] cursor-pointer w-fit" @click="open(`/item_bank_exercise/${element.itemBankExercise.id}`)">{{ element.itemBankExercise.title }}</div>
                    </a-tooltip>
                    <div class="flex space-x-12">
                      <img :src="element.operateUser.avatar.middle" class="w-40 h-40" draggable="false" alt="" style="border-radius: 9999px;">
                      <div class="flex flex-col">
                        <div class="text-12 font-medium text-[#1D2129] leading-20 max-w-250 w-fit truncate">{{ element.operateUser.nickname }}</div>
                        <div class="text-12 font-normal text-[#86909C] leading-20 max-w-250 w-fit truncate">{{ element.roles }}</div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="flex flex-col justify-between items-end">
                  <div class="text-20 font-semibold text-[#FF7E56] whitespace-nowrap"><span class="text-12 mr-2">¥</span>{{ `${integerPart(element.itemBankExercise.price)}.` }}<span class="text-12">{{ decimalPart(element.itemBankExercise.price) }}</span></div>
                  <div class="flex">
                    <div class="text-12 text-[#919399] font-normal whitespace-nowrap"><span class="text-[#37393D] mr-2">{{ element.itemBankExercise.studentNum }}</span>{{ t('label.students') }}</div>
                    <div class="mx-6 text-[#E5E6EB] text-12">|</div>
                    <div class="text-12 text-[#919399] font-normal whitespace-nowrap"><span class="text-[#37393D] mr-2">{{ element.chapterExerciseNum }}</span>{{ t('label.chapterExercises') }}</div>
                    <div class="mx-6 text-[#E5E6EB] text-12">|</div>
                    <div class="text-12 text-[#919399] font-normal whitespace-nowrap"><span class="text-[#37393D] mr-2">{{ element.assessmentNum }}</span>{{ t('label.testPaperPractice') }}</div>
                    <div class="mx-6 text-[#E5E6EB] text-12">|</div>
                    <div class="text-12 text-[#919399] font-normal whitespace-nowrap">{{ t('label.periodOfValidity') }}
                      <span class="text-[#37393D] mr-2" v-if="element.itemBankExercise.expiryMode === 'forever'">{{ t('label.longTermEffective') }}</span>
                      <span class="text-[#37393D] mr-2" v-if="element.itemBankExercise.expiryMode === 'date' || element.itemBankExercise.expiryMode === 'end_date'">{{ t('label.expiryEndDate', {expiryEndDate: formatDate(element.itemBankExercise.expiryEndDate, 'YYYY-MM-DD')}) }}</span>
                      <span class="text-[#37393D] mr-2" v-if="element.itemBankExercise.expiryMode === 'days'">{{ t('label.expiryEndDate', {expiryDays: element.itemBankExercise.expiryDays}) }}</span>
                    </div>
                  </div>
                  <a-button size="small" @click="showDeleteConfirm(element.id)">{{ t('btn.delete') }}</a-button>
                </div>
              </div>
            </template>
          </draggable>
        </div>
      </a-spin>
    </div>
    <ItemBankDrawer v-model:itemBankListVisible="itemBankListVisible" :bind-id="props.bindId" :bind-type="props.bindType" :bind-item-bank-exercise-num="bindItemBankExerciseNum" @need-get-bind-item-bank="getBindItemBankExercise"/>
  </AntConfigProvider>
</template>
