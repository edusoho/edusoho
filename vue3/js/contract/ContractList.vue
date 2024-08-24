<script setup>
import {reactive, ref} from 'vue';
import {ContractApi} from '../../api/Contract.js';
import {formatDate} from 'vue3/js/common';
import {message} from 'ant-design-vue';

const contractManagementColumns = [
  {
    key: 'name',
    title: '名称',
    dataIndex: 'name',
    width: 400,
    ellipsis: true,
  },
  {
    key: 'relatedGoods',
    title: '关联商品',
    dataIndex: 'relatedGoods',
    width: 200,
  },
  {
    key: 'updatedUser',
    title: '更新人',
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
    width: 200,
  },
];

const keyword = ref('');
const keywordType = ref('name');

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
    }, {keyword: keyword.value, keywordType: keywordType.value}) : params;
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
  keywordType.value = 'name';
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

// const drawerVisible = ref(false);
// const drawerType = ref('');
// const showDrawer = (type) => {
//   drawerVisible.value = true;
//   drawerType.value = type;
// };

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
  message.success('删除成功');
  await getList();
};

const signatureContent = ref();
const signatureContentVisible = ref(false);
const selectedSignatureContract = ref({});
const view = async (record) => {
  selectedSignatureContract.value = record;
  console.log(selectedSignatureContract.value)
  signatureContent.value = await ContractApi.getContract(record.id);
  signatureContentVisible.value = true;
}
</script>

<template>

  <div class="flex flex-col space-y-24">
    <div class="space-x-20">
      <a-select v-model:value="keywordType" style="width: 140px" placeholder="搜索类型" allow-clear>
        <a-select-option value="name">名称</a-select-option>
        <a-select-option value="username">更新人</a-select-option>
      </a-select>
      <a-input v-model:value="keyword" placeholder="请输入名称" style="width: 360px"></a-input>
      <a-button type="primary" ghost @click="onSearch">搜索</a-button>
      <a-button @click="onReset">重置</a-button>
    </div>
    <a-table
      :columns="contractManagementColumns"
      :data-source="pageData"
      :row-key="record => record.id"
      :pagination="false"
      :loading="loading"
      :scroll="{ x: 1210 }"
      @change="handleTableChange"
    >
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'relatedGoods'">
          <div class="flex flex-col items-start">
            <span>{{ `课程：${record.relatedGoodsCount ? record.relatedGoodsCount.course ?? 0 : 0}` }}</span>
            <span>{{ `班级：${record.relatedGoodsCount ? record.relatedGoodsCount.classroom ?? 0 : 0}` }}</span>
            <span>{{ `题库：${record.relatedGoodsCount ? record.relatedGoodsCount.itemBankExercise ?? 0 : 0}` }}</span>
          </div>
        </template>
        <template v-else-if="column.key === 'operation'">
          <div class="flex">
            <a-button type="link" @click="view(record)">查看</a-button>
            <a-button type="link" @click="">编辑</a-button>
            <a-button type="link" @click="onDelete(record.id)">删除</a-button>
          </div>
        </template>
        <template v-else-if="column.key === 'updatedUser'">
          {{ record.updatedUser ? record.updatedUser.nickname : '' }}
        </template>
        <template v-if="column.key === 'updatedTime'">
          {{ formatDate(record.updatedTime) }}
        </template>
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
    <a-modal :width="900"
             v-model:open="signatureContentVisible"
             :title="`电子合同签署`"
             :bodyStyle="{'height': 'fit-content', 'max-height': '500px', 'overflow': 'auto'}"
    >
      <div class="w-full flex flex-col space-y-32 p-32">
        <div class="flex items-end justify-between gap-4">
          <span class="flex-none whitespace-nowrap opacity-0 mr-100">合同编号:  </span>
          <span class="grow text-center text-22 font-medium">{{ signatureContent.name }}</span>
          <span class="flex-none whitespace-nowrap text-gray-500 mr-100">合同编号:  </span>
        </div>
        <div v-html="signatureContent.content" class="text-gray-500"></div>
        <div class="flex space-x-64">
          <div class="flex-1 flex flex-col items-start justify-between space-y-22">
            <span class="text-18 font-medium">甲方：</span>
            <div class="w-full flex flex-col space-y-22">
              <img :src="signatureContent.seal" alt="甲方印章" class="w-150 h-150" />
              <div class="flex items-center">
                <span class="text-gray-500">签约日期：</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium mt-20"></div>
              </div>
            </div>
          </div>
          <div class="flex-1 flex flex-col items-start justify-between">
            <span class="text-18 font-medium">乙方：</span>
            <div class="w-full flex flex-col space-y-22">
              <div v-if="signatureContent.sign && signatureContent.sign.handSignature" class="flex items-center">
                <span class="text-gray-500">手写签名：</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium mt-20"></div>
              </div>
              <div v-if="signatureContent.sign && signatureContent.sign.truename" class="flex items-center">
                <span class="text-gray-500">乙方姓名：</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium mt-20"></div>
              </div>
              <div v-if="signatureContent.sign && signatureContent.sign.IDNumber" class="flex items-center">
                <span class="text-gray-500">身份证号：</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium mt-20"></div>
              </div>
              <div v-if="signatureContent.sign && signatureContent.sign.phoneNumber" class="flex items-center">
                <span class="text-gray-500">联系方式：</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium mt-20"></div>
              </div>
              <div class="flex items-center">
                <span class="text-gray-500">签约日期：</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium mt-20"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <template #footer>
        <div class="flex justify-center">
          <a-button @click="signatureContentVisible = false">关闭</a-button>
        </div>
      </template>
    </a-modal>
  </div>
  <!--  <contract-drawer v-model:visible="drawerVisible" :type="drawerType"/>-->
</template>
