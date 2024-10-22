<script setup>
import {computed, onBeforeMount, reactive, ref, watch} from 'vue';
import { useInfiniteScroll } from '@vueuse/core'
import { message } from 'ant-design-vue';
import {CloseOutlined} from '@ant-design/icons-vue';
import Api from '../../../api';

const emit = defineEmits(['needGetBindItemBank'])
const itemBankListVisible = defineModel('itemBankListVisible');
const props = defineProps({
  bindId: {
    required: true,
  },
  bindType: {
    required: true,
  },
  bindItemBankExerciseNum: {
    required: true,
  }
})

function closeItemBankList() {
  itemBankListVisible.value = false;
}

const itemBankCategoryOptions = ref();
const keywordTypeOptions = ref([
  { label: '名称', value: 'title' },
  { label: '更新人', value: 'updateUser' },
]);

const categoryId = ref();
const keywordType = ref('title');
const keyword = ref('');

const itemBankExerciseData = ref([]);
const itemBankExerciseState = ref([]);
function transformItemBankExerciseState(itemBankExerciseData) {
  return  itemBankExerciseData.map(item => ({
    id: item.id,
    checked: false
  }));
}

const pagination = reactive({
  current: 1,
  pageSize: 10,
});

function transformItemBankCategory(data) {
  return data.map(item => {
    const transformedItem = {
      label: item.name,
      value: item.id,
    };
    if (item.children && item.children.length > 0) {
      transformedItem.children = transformItemBankCategory(item.children);
    }
    return transformedItem;
  });
}

async function fetchItemBankExercise(params) {
  const searchQuery = Object.assign({bindId: props.bindId, bindType: props.bindType, categoryId: categoryId.value ? categoryId.value : '', ...params}, keywordType.value === 'title' ? {title: keyword.value} : {updatedUser: keyword.value});
  const { data, paging } = await Api.itemBank.searchItemBank(searchQuery);
  return data;
}

let allDataLoaded = false;
const itemBankTableBody = ref(null);
const { reset } = useInfiniteScroll(
  itemBankTableBody,
  async () => {
    const params = {
      limit: pagination.pageSize,
      offset: (pagination.current - 1) * pagination.pageSize,
    };
    const newData = await fetchItemBankExercise(params);
    if (newData.length === 0) {
      allDataLoaded = true;
    }
    itemBankExerciseData.value.push(...newData);
    itemBankExerciseState.value.push(...transformItemBankExerciseState(newData));
    pagination.current += 1;
  },
  { distance: 20, canLoadMore: () => {
    return !allDataLoaded;
    }},
)

function formattedDate(dateStr) {
  const date = new Date(dateStr);
  return date.getFullYear() + '-' +
    String(date.getMonth() + 1).padStart(2, '0') + '-' +
    String(date.getDate()).padStart(2, '0') + ' ' +
    String(date.getHours()).padStart(2, '0') + ':' +
    String(date.getMinutes()).padStart(2, '0') + ':' +
    String(date.getSeconds()).padStart(2, '0');
}

async function search() {
  reset();
  pagination.current = 1;
  const params = {
    limit: pagination.pageSize,
    offset: (pagination.current - 1) * pagination.pageSize,
  }
  const newDate = await fetchItemBankExercise(params)
  itemBankExerciseData.value = newDate;
  itemBankExerciseState.value = transformItemBankExerciseState(newDate);
}

async function clear() {
  itemBankExerciseData.value = [];
  itemBankExerciseState.value = [];
  reset();
  categoryId.value = undefined;
  keywordType.value = 'title';
  keyword.value = undefined;
}

function toItemBankExercisePage(exerciseId) {
  window.location.href = `/item_bank_exercise/${exerciseId}`
}

const checkedExerciseIdNum = computed(() => {
  return itemBankExerciseState.value
    .filter(item => item.checked === true).length;
})

const isSelectAll = computed(() => {
  if (itemBankExerciseData.value.length === 0) {
    return false;
  }
  const exerciseIds = itemBankExerciseData.value.map(item => item.id)
  const checkedExerciseIds = itemBankExerciseState.value
    .filter(item => item.checked === true)
    .map(item => item.id);
  for (const id of exerciseIds) {
    if (!checkedExerciseIds.includes(id)) {
      return false;
    }
  }
  return true;
})

const isIndeterminate = computed(() => {
  if (checkedExerciseIdNum.value === 0) {
    return false
  }
  const exerciseIds = itemBankExerciseData.value.map(item => item.id)
  const checkedExerciseIds = itemBankExerciseState.value
    .filter(item => item.checked === true)
    .map(item => item.id);
  for (const id of exerciseIds) {
    if (!checkedExerciseIds.includes(id)) {
      return true;
    }
  }
  return false;
})

const needResetCheckbox = ref(false);
function handleSelectAllChange(e) {
  const isChecked = e.target.checked;
  if (isChecked && needResetCheckbox.value) {
    resetCheckboxes();
    needResetCheckbox.value = false;
    return;
  }
  const limit = 100 - props.bindItemBankExerciseNum;
  itemBankExerciseState.value = itemBankExerciseState.value.map((item, index) => ({
    ...item,
    checked: isChecked
      ? (props.bindItemBankExerciseNum + itemBankExerciseState.value.length >= 100 ? index < limit : true)
      : false
  }));
  if (isChecked) needResetCheckbox.value = true;
}
function resetCheckboxes() {
  itemBankExerciseState.value = itemBankExerciseState.value.map(item => ({
    ...item,
    checked: false,
  }));
}

async function bindItemBankExercise() {
  const exerciseIds = itemBankExerciseState.value
    .filter(item => item.checked === true)
    .map(item => item.id);
  const params = {
    bindType: props.bindType,
    bindId: props.bindId,
    exerciseIds: exerciseIds
  }
  await Api.itemBank.bindItemBankExercise(params);
  closeItemBankList();
  emit('needGetBindItemBank');
}

function checkboxIsDisabled(item) {
  return !item.checked && props.bindItemBankExerciseNum + checkedExerciseIdNum.value >= 100;
}

watch(() => props.bindItemBankExerciseNum + checkedExerciseIdNum.value, (newValue) => {
  if (newValue === 100) {
    message.error('最多可绑定100个题库练习');
  }
})

onBeforeMount(async () => {
  itemBankCategoryOptions.value = transformItemBankCategory(await Api.itemBank.getItemBankCategory());
})
</script>

<template>
  <a-drawer
    v-model:open="itemBankListVisible"
    placement="right"
    :closable="false"
    :maskClosable="false"
    :bodyStyle="{padding: 0}"
    width="60vw"
  >
    <div class="flex flex-col relative h-full">
      <div class="flex justify-between px-20 py-14 border border-x-0 border-t-0 border-[#EFF0F5] border-solid">
        <div class="font-medium text-16 text-[#37393D]">绑定题库</div>
        <CloseOutlined @click="closeItemBankList"/>
      </div>
      <div class="flex flex-col px-20 pt-24">
        <div class="flex space-x-20 mb-20">
          <a-tree-select
            v-model:value="categoryId"
            :show-search="true"
            placeholder="题库分类"
            allow-clear
            tree-default-expand-all
            :tree-data="itemBankCategoryOptions"
            :style="{ minWidth: '212px'}"
          >
          </a-tree-select>
          <a-select
            v-model:value="keywordType"
            :options="keywordTypeOptions"
            :style="{ minWidth: '112px' }"
          >
          </a-select>
          <a-input
            v-model:value="keyword"
            allow-clear
            :placeholder="keywordType === 'title' ? '请输入名称' : '请输入更新人'"
          >
          </a-input>
          <a-button type="primary" ghost @click="search">搜索</a-button>
          <a-button @click="clear">重置</a-button>
        </div>
        <div class="flex flex-col w-full">
          <div class="flex rounded-t-4 bg-[#F5F5F5] w-full">
            <div class="flex items-center w-[15%]">
              <a-checkbox class="py-16 px-8" :indeterminate="isIndeterminate" :checked="checkedExerciseIdNum > 0 && isSelectAll" @change="handleSelectAllChange"/>
              <div class="text-14 text-[#37393D] font-medium px-16 py-16">编号</div>
            </div>
            <div class="flex items-center px-16 py-13 w-[30%]">
              <div class="text-14 text-[#37393D] font-medium">名称</div>
            </div>
            <div class="flex items-center flex-row-reverse px-16 py-13 w-[10%]">
              <div class="text-14 text-[#37393D] font-medium">价格(元)</div>
            </div>
            <div class="flex items-center flex-row-reverse px-16 py-13 w-[10%]">
              <div class="text-14 text-[#37393D] font-medium">学员数</div>
            </div>
            <div class="flex items-center px-16 py-13 w-[15%]">
              <div class="text-14 text-[#37393D] font-medium">更新人</div>
            </div>
            <div class="flex items-center px-16 py-13 w-[20%]">
              <div class="text-14 text-[#37393D] font-medium">更新时间</div>
            </div>
          </div>
          <div ref="itemBankTableBody" class="flex flex-col w-full overflow-y-scroll h-[calc(100vh-248px)]">
            <div v-if="itemBankExerciseData.length > 0" v-for="(record, index) in itemBankExerciseData">
              <div class="flex border border-x-0 border-t-0 border-solid border-[#EFF0F5]">
                <div class="flex items-center w-[15%]">
                  <a-checkbox class="py-16 px-8" v-model:checked="itemBankExerciseState[index].checked" :disabled="checkboxIsDisabled(itemBankExerciseState[index])" @change="needResetCheckbox = false"/>
                  <div class="text-14 text-[#37393D] font-normal px-16 py-16">{{ record.id }}</div>
                </div>
                <div class="flex flex-col px-16 py-16 w-[30%]">
                  <a-tooltip placement="topLeft">
                    <template #title>
                      <div class="max-w-216">{{ record.title }}</div>
                    </template>
                    <div class="text-14 text-[#37393D] font-normal w-fit max-w-280 truncate mb-4 hover:text-[#18AD3B] hover:cursor-pointer" @click="toItemBankExercisePage(record.id)">{{ record.title }}</div>
                  </a-tooltip>
                  <div class="text-12 text-[#919399] w-fit">分类:</div>
                </div>
                <div class="flex flex-row-reverse px-16 py-16 w-[10%]">
                  <div class="text-14 text-[#37393D] font-normal">{{ record.price }}</div>
                </div>
                <div class="flex flex-row-reverse px-16 py-16 w-[10%]">
                  <div class="text-14 text-[#37393D] font-normal">{{ record.studentNum }}</div>
                </div>
                <div class="flex px-16 py-16 w-[15%]">
                  <div class="text-14 text-[#37393D] font-normal" v-if="record.updatedUser">{{ record.updatedUser.nickname }}</div>
                </div>
                <div class="flex px-16 py-16 w-[20%]">
                  <div class="text-14 text-[#37393D] font-normal">{{ formattedDate(record.updatedTime) }}</div>
                </div>
              </div>
            </div>
            <div v-else class="flex items-center justify-center w-full h-full">
              <a-empty description="暂无题库"/>
            </div>
          </div>
        </div>
      </div>
      <div class="fixed bottom-0 right-0 bg-white w-[60vw] flex items-center justify-between px-28 py-16 border border-x-0 border-b-0 border-[#EFF0F5] border-solid">
        <div class="flex space-x-24">
          <a-checkbox :indeterminate="isIndeterminate && !isSelectAll" :checked="checkedExerciseIdNum > 0 && isSelectAll" @change="handleSelectAllChange"/>
          <div class="text-[#37393D] text-14 font-normal">全选</div>
          <div class="text-[#37393D] text-14 font-normal">{{ `选择 ${ checkedExerciseIdNum } 项` }}</div>
        </div>
        <div class="space-x-16">
          <a-button @click="closeItemBankList">取消</a-button>
          <a-button type="primary" @click="bindItemBankExercise">确认</a-button>
        </div>
      </div>
    </div>
  </a-drawer>
</template>
