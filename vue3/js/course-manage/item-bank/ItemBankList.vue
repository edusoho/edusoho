<script setup>
import {ref} from 'vue';

const itemBankListVisible = defineModel('itemBankListVisible');
import {CloseOutlined} from '@ant-design/icons-vue';

const closeItemBankList = () => {
  itemBankListVisible.value = false;
}

const classificationOptions = ref([]);
const keywordTypeOptions = ref([
  { label: '名称', value: 'name' },
  { label: '更新人', value: 'updater' },
]);

const classification = ref();
const keywordType = ref('name');
const keyword = ref('');

const itemBankColumns = [
  {
    title: '编号'
  },
  {
    title: '名称',
    width: 300,
    ellipsis: true,
  },
  {
    title: '价格（元）',
    fixed: 'right',
  },
  {
    title: '学员数',
    fixed: 'right',
  },
  {
    title: '更新人'
  },
  {
    title: '更新时间'
  },
]

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
</script>

<template>
  <a-drawer
    v-model:open="itemBankListVisible"
    placement="right"
    :closable="false"
    :maskClosable="false"
    bodyStyle="padding: 0"
    width="60vw"
  >
    <div class="flex flex-col relative h-full">
      <div class="flex justify-between px-20 py-14 border-x-0 border-t-0 border-[#EFF0F5] border-solid">
        <div class="font-medium text-16 text-[#37393D]">绑定题库</div>
        <CloseOutlined @click="closeItemBankList"/>
      </div>
      <div class="flex flex-col px-20 py-24">
        <div class="flex space-x-20 mb-20">
          <a-tree-select
            v-model:value="classification"
            :show-search="true"
            placeholder="题库分类"
            allow-clear
            tree-default-expand-all
            :tree-data="classificationOptions"
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
        <a-table :columns="itemBankColumns" :row-selection="rowSelection"/>
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
