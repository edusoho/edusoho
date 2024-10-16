<script setup>
import {onBeforeMount, ref} from 'vue';

const props = defineProps({
  bindId: {
    required: true,
  },
  bindType: {
    required: true,
  }
})

const itemBankListVisible = defineModel('itemBankListVisible');
import {CloseOutlined} from '@ant-design/icons-vue';
import Api from '../../../api';

const closeItemBankList = () => {
  itemBankListVisible.value = false;
}

const loading = ref(false);
const itemBankCategoryOptions = ref();
const keywordTypeOptions = ref([
  { label: '名称', value: 'title' },
  { label: '更新人', value: 'updateUser' },
]);

const categoryId = ref();
const keywordType = ref('title');
const keyword = ref('');

const itemBankExerciseData = ref();
const itemBankExerciseColumns = [
  {
    key: 'id',
    title: '编号',
    dataIndex: 'id',
  },
  {
    key: 'title',
    title: '名称',
    width: 300,
    ellipsis: true,
  },
  {
    key: 'price',
    title: '价格（元）',
    dataIndex: 'price',
  },
  {
    key: 'studentNum',
    title: '学员数',
    dataIndex: 'studentNum',
  },
  {
    title: '更新人'
  },
  {
    key: 'updatedTime',
    title: '更新时间',
    dataIndex: 'updatedTime'
  },
];

const rowSelection = ref({
  checkStrictly: false,
  onChange: (selectedRowKeys, selectedRows) => {
    console.log(`selectedRowKeys: ${selectedRowKeys}`, 'selectedRows: ', selectedRows);
  },
  onSelect: (record, selected, selectedRows) => {
    console.log(record, selected, selectedRows);
  },
  onSelectAll: (selected, selectedRows, changeRows) => {
    console.log(selected, selectedRows, changeRows);
  },
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

async function fetchItemBankExercise() {
  const searchQuery = Object.assign({bindId: props.bindId, bindType: props.bindType, categoryId: categoryId.value ? categoryId.value : ''}, keywordType.value === 'title' ? {title: keyword.value} : {updateUser: keyword.value});
  const { data, paging } = await Api.itemBank.search(searchQuery);
  itemBankExerciseData.value = data;
  console.log(itemBankExerciseData.value);
}

onBeforeMount(async () => {
  itemBankCategoryOptions.value = transformItemBankCategory(await Api.itemBank.getItemBankCategory());
  await fetchItemBankExercise();
})
</script>

<template>
  <a-drawer
    v-model:open="itemBankListVisible"
    placement="right"
    :closable="false"
    :maskClosable="false"
    :bodyStyle="{padding: 0}"
    width="70vw"
  >
    <div class="flex flex-col relative h-full">
      <div class="flex justify-between px-20 py-14 border-x-0 border-t-0 border-[#EFF0F5] border-solid">
        <div class="font-medium text-16 text-[#37393D]">绑定题库</div>
        <CloseOutlined @click="closeItemBankList"/>
      </div>
      <div class="flex flex-col px-20 py-24">
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
            :placeholder="keywordType === 'name' ? '请输入名称' : '请输入更新人'"
          >
          </a-input>
          <a-button type="primary" ghost>搜索</a-button>
          <a-button>重置</a-button>
        </div>
        <a-table :columns="itemBankExerciseColumns"
                 :data-source="itemBankExerciseData"
                 :row-selection="rowSelection"
                 :loading="loading"
                 :row-key="record => record.id"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'title'">
              <div class="flex flex-col">
                <div>{{record.title}}</div>
                <div>{{record.title}}</div>
              </div>
            </template>
          </template>
        </a-table>
      </div>
      <div class="absolute bottom-0 left-0 bg-white w-full flex items-center justify-between px-36 py-16 border-x-0 border-b-0 border-[#EFF0F5] border-solid">
        <div class="flex space-x-12">
          <a-checkbox/>
          <div class="text-[#37393D] text-14 font-normal">全选</div>
          <div class="text-[#37393D] text-14 font-normal">选择...项</div>
        </div>
        <div class="space-x-16">
          <a-button @click="closeItemBankList">取消</a-button>
          <a-button type="primary">确认</a-button>
        </div>
      </div>
    </div>
  </a-drawer>
</template>

<style lang="less">
  .ant-table-cel {
    border-bottom: 0;
  }
</style>
