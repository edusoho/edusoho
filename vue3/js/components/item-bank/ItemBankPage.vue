<script setup>
import {computed, createVNode, onBeforeMount, ref} from 'vue';
import AntConfigProvider from '../AntConfigProvider.vue';
import ItemBankDrawer from './ItemBankDrawer.vue';
import {ExclamationCircleOutlined, InfoCircleOutlined} from '@ant-design/icons-vue';
import {message, Modal} from 'ant-design-vue';
import Api from '../../../api';
import draggable from 'vuedraggable';
import {formatDate} from '../../common';

const props = defineProps({
  bindType: {required: true},
  bindId: {required: true},
})

const bindItemBankExerciseList = ref([]);
const bindItemBankExerciseNum = computed(() => {
  return bindItemBankExerciseList.value.length;
})

const itemBankListVisible = ref(false);
const showItemBankList = () => {
  if (bindItemBankExerciseNum.value >= 100) {
    message.error('已超出上限，最多可绑定100个题库练习');
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
  } finally {
    loading.value = false;
  }
}

async function sequenceItemBankExerciseBind() {
  const params = {
    ids: bindItemBankExerciseList.value.map(item => item.id),
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
    title: '移除题库练习',
    icon: createVNode(ExclamationCircleOutlined),
    content: createVNode('div', { style: 'font-size: 14px; color: #5E6166; font-weight: 400; ' }, '是否要从班级中移除该题库练习？'),
    async onOk() {
      await deleteBindItemBank(id)
      message.success('删除成功');
    },
    onCancel() {
    },
  });
}

function toItemBankExercisePage(exerciseId) {
  window.location.href = `/item_bank_exercise/${exerciseId}?bindId=${props.bindId}&bindType=${props.bindType}`
}

onBeforeMount(async () => {
  await getBindItemBankExercise();
})
</script>

<template>
  <AntConfigProvider>
    <div class="flex flex-col px-32 pt-20">
      <div class="flex justify-between items-center mb-20">
        <div class="text-16 font-medium text-black/[.88]">题库练习管理</div>
        <a-tooltip placement="topLeft">
          <template #title>
            <div class="w-216">{{ props.bindType === 'course' ? '绑定后课程学员自动加入题库练习，学员在课程内的学习有效期和学习权限同步影响其在题库练习内的有效期和答题权限。' : '绑定后班级学员可同步加入到题库练习内（包括在绑定前加入班级的学员），班级学员获得绑定的题库练习的学员权限。' }}</div>
          </template>
          <a-button type="primary" class="flex items-center" @click="showItemBankList">
            <InfoCircleOutlined style="font-size: 16px"/>
            <div class="ml-8 text-14 font-normal">绑定题库</div>
          </a-button>
        </a-tooltip>
      </div>
      <a-spin :spinning="loading" tip="加载中...">
        <div v-if="bindItemBankExerciseList.length === 0">
          <a-empty description="暂无已绑定的题库" class="mt-150"/>
        </div>
        <div v-else class="mb-20">
          <draggable
            v-model="bindItemBankExerciseList"
            group="people"
            @end="sequenceItemBankExerciseBind"
            item-key="id">
            <template #item="{element}">
              <div class="flex justify-between px-24 py-16 border border-[#DFE2E6] border-solid rounded-6 mb-16 bg-white">
                <div class="flex space-x-16">
                  <div class="flex items-center">
                    <img src="../../../img/item-bank/list-icon.png" class="w-16" draggable="false" alt="">
                  </div>
                  <div class="relative">
                    <img :src="element.itemBankExercise.cover.middle" class="h-90 rounded-5" draggable="false" alt="">
                    <div v-if="element.itemBankExercise.status === 'published'" class="text-12 text-white font-medium px-8 py-2 bg-[#00C261] rounded-tl-5 rounded-br-5 leading-20 absolute top-0 left-0">已发布</div>
                    <div v-if="element.itemBankExercise.status === 'closed'" class="text-12 text-white font-medium px-8 py-2 rounded-tl-5 rounded-br-5 leading-20 absolute top-0 left-0 bg-[rgba(0,0,0,0.6)]">已关闭</div>
                  </div>
                  <div class="flex flex-col justify-between">
                    <a-tooltip placement="top" :title="element.itemBankExercise.title">
                      <div class="text-16 font-medium text-[#37393D] max-w-250 truncate hover:text-[#18AD3B] cursor-pointer w-fit" @click="toItemBankExercisePage(element.itemBankExercise.id)">{{ element.itemBankExercise.title }}</div>
                    </a-tooltip>
                    <div class="flex space-x-12">
                      <img :src="element.operateUser.avatar.middle" class="w-40 h-40" draggable="false" alt="" style="border-radius: 9999px;">
                      <div class="flex flex-col">
                        <div class="text-12 font-medium text-[#1D2129] leading-20">{{ element.operateUser.nickname }}</div>
                        <div class="text-12 font-normal text-[#86909C] leading-20">测试</div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="flex flex-col justify-between items-end">
                  <div class="text-20 font-semibold text-[#FF7E56]"><span class="text-12 mr-2">¥</span>{{ `${integerPart(element.itemBankExercise.price)}.` }}<span class="text-12">{{ decimalPart(element.itemBankExercise.price) }}</span></div>
                  <div class="flex">
                    <div class="text-12 text-[#919399] font-normal"><span class="text-[#37393D] mr-2">{{ element.itemBankExercise.studentNum }}</span>学员</div>
                    <div class="mx-6 text-[#E5E6EB] text-12">|</div>
                    <div class="text-12 text-[#919399] font-normal"><span class="text-[#37393D] mr-2">{{ element.chapterExerciseNum }}</span>章节练习</div>
                    <div class="mx-6 text-[#E5E6EB] text-12">|</div>
                    <div class="text-12 text-[#919399] font-normal"><span class="text-[#37393D] mr-2">{{ element.assessmentNum }}</span>试卷练习</div>
                    <div class="mx-6 text-[#E5E6EB] text-12">|</div>
                    <div class="text-12 text-[#919399] font-normal">有效期：
                      <span class="text-[#37393D] mr-2" v-if="element.itemBankExercise.expiryMode === 'forever'">长期有效</span>
                      <span class="text-[#37393D] mr-2" v-if="element.itemBankExercise.expiryMode === 'date' || element.itemBankExercise.expiryMode === 'end_date'">{{ `截止至 ${formatDate(element.itemBankExercise.expiryEndDate, 'YYYY-MM-DD')}` }}</span>
                      <span class="text-[#37393D] mr-2" v-if="element.itemBankExercise.expiryMode === 'days'">{{ `共 ${element.itemBankExercise.expiryDays} 天` }}</span>
                    </div>
                  </div>
                  <a-button size="small" @click="showDeleteConfirm(element.id)">删除</a-button>
                </div>
              </div>
            </template>
          </draggable>
        </div>
      </a-spin>
    </div>
    <ItemBankDrawer v-if="itemBankListVisible" v-model:itemBankListVisible="itemBankListVisible" :bind-id="props.bindId" :bind-type="props.bindType" :bind-item-bank-exercise-num="bindItemBankExerciseNum" @need-get-bind-item-bank="getBindItemBankExercise"/>
  </AntConfigProvider>
</template>
