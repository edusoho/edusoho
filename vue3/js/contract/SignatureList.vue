<script setup>
import {reactive, ref} from 'vue';
import {ContractApi} from '../../api/Contract.js';

const columns = [
  {
    key: 'name',
    title: '合同编号',
    dataIndex: 'name',
    width: 400,
    ellipsis: true,
  },
  {
    key: 'relatedGoods',
    title: '用户名',
    dataIndex: 'relatedGoods',
    width: 200,
  },
  {
    key: 'updatedUser',
    title: '手机号',
    dataIndex: 'updatedUser.nickname',
    width: 150,
  },
  {
    key: 'updatedUser',
    title: '商品类型',
    dataIndex: 'updatedUser.nickname',
    width: 150,
  },
  {
    key: 'updatedUser',
    title: '商品名称',
    dataIndex: 'updatedUser.nickname',
    width: 150,
  },
  {
    key: 'updatedUser',
    title: '电子合同名称',
    dataIndex: 'updatedUser.nickname',
    width: 150,
  },
  {
    key: 'updatedTime',
    title: '更新时间',
    width: 210,
  },
  {
    key: 'operation',
    title: '操作',
    width: 100,
  },
];

const keyword = ref('');
const keywordType = ref('userName');

const pagination = reactive({
  current: 1,
  total: 0,
  pageSize: 10,
});
const pageSizeOptions = ['10', '20', '30', '40', '50'];

const loading = ref(false);
const pageData = ref([]);

async function fetchContracts(params) {
  loading.value = true;
  const searchQuery = keyword.value ? Object.assign({
      ...params
    }, keywordType.value === 'userName' ? {userName: keyword.value} : {name: keyword.value}
  ) : params;
  const {data, paging} = await ContractApi.search(searchQuery);
  pagination.total = Number(paging.total);
  pagination.pageSize = Number(paging.limit);
  pageData.value = data;
  loading.value = false;
}

async function handleTableChange(paging) {
  pagination.current = paging.current;
  pagination.total = paging.total;
  pagination.pageSize = paging.pageSize;
  const params = {
    limit: paging.pageSize,
    offset: (paging.current - 1) * paging.pageSize,
  };
  await fetchContracts(params);
}

async function onSearch() {
  pagination.current = 1;
  const params = {
    limit: pagination.pageSize,
    offset: (pagination.current - 1) * pagination.pageSize,
  };
  await fetchContracts(params);
}

async function onReset() {
  pagination.current = 1;
  keywordType.value = 'userName';
  keyword.value = '';
  const params = {
    limit: pagination.pageSize,
    offset: (pagination.current - 1) * pagination.pageSize,
  };
  await fetchContracts(params);
}

function getTableTotal(total) {
  return `共 ${total} 项`;
}

async function handlePaginationChange(page, pageSize) {
  pagination.current = page;
  pagination.pageSize = pageSize;
  await handleTableChange(pagination);
}

const getList = async () => {
  const params = {
    limit: pagination.pageSize,
    offset: (pagination.current - 1) * pagination.pageSize,
  };
  await fetchContracts(params);
};
getList();

const onDelete = async (id) => {
  await ContractApi.delete(id);
  await getList();
};

const timeRange = ref();
</script>

<template>

  <div class="flex flex-col space-y-24">
    <div class="space-x-20">
      <a-select v-model:value="keywordType" style="width: 140px" placeholder="商品类型" allow-clear>
        <a-select-option value="userName">课程</a-select-option>
        <a-select-option value="phone">题库</a-select-option>
        <a-select-option value="name">班级</a-select-option>
      </a-select>
      <div class="flex items-center">
        <span>签署时间：</span>
        <a-range-picker v-model:value="timeRange" />
      </div>
      <a-select v-model:value="keywordType" style="width: 140px">
        <a-select-option value="userName">用户名</a-select-option>
        <a-select-option value="phone">手机号</a-select-option>
        <a-select-option value="name">商品名称</a-select-option>
      </a-select>
      <a-input v-model:value="keyword" placeholder="请输入名称" style="width: 360px"></a-input>
      <a-button type="primary" ghost @click="onSearch">搜索</a-button>
      <a-button @click="onReset">重置</a-button>
    </div>
    <a-table
      :columns="columns"
      :data-source="pageData"
      :row-key="record => record.id"
      :pagination="false"
      :loading="loading"
      :scroll="{ x: 1210 }"
      @change="handleTableChange"
    >
      <template #bodyCell="{ column, record }">
      </template>
    </a-table>
    <div class="flex flex-row-reverse">
      <a-pagination
        show-quick-jumper
        show-size-changer
        :page-size-options="pageSizeOptions"
        :show-total="total => getTableTotal(total)"
        v-model="pagination.current"
        :total="pagination.total"
        @showSizeChange="handlePaginationChange"
        @change="handlePaginationChange"
      />
    </div>
  </div>
</template>
