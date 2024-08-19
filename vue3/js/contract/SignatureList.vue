<script setup>
import {reactive, ref, watch} from 'vue';
import {ContractApi} from '../../api/Contract.js';
import {InfoCircleOutlined} from '@ant-design/icons-vue';
import {formatDate} from '../common';

const dateFormat = 'YYYY-MM-DD';

const columns = [
  {
    key: 'contractCode',
    title: '合同编号',
    dataIndex: 'contractCode',
    width: 140,
    ellipsis: true,
  },
  {
    key: 'username',
    title: '用户名',
    dataIndex: 'username',
    align: 'center',
    width: 150,
  },
  {
    key: 'mobile',
    title: '手机号',
    dataIndex: 'mobile',
    width: 120,
  },
  {
    key: 'goodsType',
    title: '商品类型',
    dataIndex: 'goodsType',
    width: 80,
  },
  {
    key: 'goodsName',
    title: '商品名称',
    dataIndex: 'goodsName',
    width: 150,
  },
  {
    key: 'contractName',
    title: '电子合同名称',
    dataIndex: 'contractName',
    width: 200,
  },
  {
    key: 'operation',
    title: '操作',
    width: 60,
  },
];

const goodsType = ref();
const signTime = ref();
const keyword = ref('');
const keywordType = ref('username');

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
  params = goodsType.value ? Object.assign(params, {goodsType: goodsType.value}) : params;
  if (signTime.value && signTime.value.length === 2) {
    params = Object.assign(params, {signTimeFrom: signTime.value[0].format(dateFormat), signTimeTo: signTime.value[1].format(dateFormat)})
  }
  const searchQuery = keyword.value ? Object.assign({
      ...params
    }, {keywordType: keywordType.value, keyword: keyword.value}
  ) : params;
  const {data, paging} = await ContractApi.searchSignature(searchQuery);
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
  keywordType.value = 'username';
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

const signatureContent = ref();
const signatureContentVisible = ref(false);
const selectedSignatureContract = ref({});
const view = async (record) => {
  selectedSignatureContract.value = record;
  signatureContent.value = await ContractApi.getSignatureContent(record.id);
  signatureContentVisible.value = true;
}

</script>

<template>

  <div class="flex flex-col space-y-24">
    <div class="flex items-center space-x-20">
      <a-select v-model:value="goodsType" style="width: 100px" placeholder="商品类型" allow-clear>
        <a-select-option value="course">课程</a-select-option>
        <a-select-option value="itemBankExercise">题库</a-select-option>
        <a-select-option value="classroom">班级</a-select-option>
      </a-select>
      <div class="flex items-center">
        <span>签署时间：</span>
        <a-range-picker v-model:value="signTime" />
      </div>
      <a-select v-model:value="keywordType" style="width: 100px">
        <a-select-option value="username">用户名</a-select-option>
        <a-select-option value="mobile">手机号</a-select-option>
        <a-select-option value="goodsName">商品名称</a-select-option>
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
      <template #headerCell="{ column }">
        <template v-if="column.key === 'goodsName'">
          <span>
            商品名称
            <a-tooltip placement="topLeft" title="管理员手动加入课程/班级/题库的学员，如果没有生成订单，这里不展示订单号">
              <info-circle-outlined />
            </a-tooltip>
          </span>
        </template>
      </template>
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'goodsName'">
          {{ record.goodsName }}
          <br>
          <span class="text-gray-500">{{ `订单号：${record.orderSn}` }}</span>
        </template>
        <template v-else-if="column.key === 'contractName'">
          {{ record.goodsName }}
          <br>
          <span class="text-gray-500">{{ `签署时间：${formatDate(record.signTime)}` }}</span>
        </template>
        <template v-else-if="column.key === 'operation'">
          <a-button type="link" @click="view(record)">查看</a-button>
        </template>
        <template v-else-if="column.key === 'mobile'">
          {{ record.mobile ? record.mobile : '-' }}
        </template>
        <template v-else-if="column.key === 'goodsType'">
          {{ record.goodsType === 'course' ? '课程' : record.goodsType === 'classroom' ? '班级' : record.goodsType === 'itemBankExercise' ? '题库' : record.goodsType  }}
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
  </div>
  <a-modal :width="900"
           v-model:open="signatureContentVisible"
           :title="`${selectedSignatureContract.goodsName}-${selectedSignatureContract.username}-电子合同签署`"
           :bodyStyle="{'height': 'fit-content', 'max-height': '500px', 'overflow': 'auto'}"
  >
    <div class="w-full flex flex-col space-y-32 p-32">
      <div class="flex items-center justify-between">
        <span class="opacity-0">{{ `合同编号: ${signatureContent.code}` }}</span>
        <span class="text-22 font-medium">{{ signatureContent.name }}</span>
        <span class="text-gray-500">{{ `合同编号: ${signatureContent.code}` }}</span>
      </div>
      <div class="text-gray-500">{{ signatureContent.content }}</div>
      <div class="flex space-x-64">
        <div class="flex-1 flex flex-col items-start justify-between space-y-22">
          <span class="text-18 font-medium">甲方：</span>
          <div class="w-full flex flex-col space-y-22">
            <img :src="signatureContent.seal" alt="甲方印章" class="w-150 h-150" />
            <div class="flex items-center">
              <span class="text-gray-500">签约日期：</span>
              <div class="grow border-solid border-0 border-b border-gray-300 font-medium">{{ signatureContent.signDate }}</div>
            </div>
          </div>
        </div>
        <div class="flex-1 flex flex-col items-start justify-between">
          <span class="text-18 font-medium">乙方：</span>
          <div class="w-full flex flex-col space-y-22">
            <div v-if="signatureContent.sign && signatureContent.sign.handSignature" class="flex items-center">
              <span class="text-gray-500">手写签名：</span>
              <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                <img :src="signatureContent.sign.handSignature" class="h-35" alt="手写签名"/>
              </div>
            </div>
            <div v-if="signatureContent.sign && signatureContent.sign.truename" class="flex items-center">
              <span class="text-gray-500">乙方姓名：</span>
              <div class="grow border-solid border-0 border-b border-gray-300 font-medium">{{ signatureContent.sign.truename }}</div>
            </div>
            <div v-if="signatureContent.sign && signatureContent.sign.IDNumber" class="flex items-center">
              <span class="text-gray-500">身份证号：</span>
              <div class="grow border-solid border-0 border-b border-gray-300 font-medium">{{ signatureContent.sign.IDNumber }}</div>
            </div>
            <div v-if="signatureContent.sign && signatureContent.sign.phoneNumber" class="flex items-center">
              <span class="text-gray-500">联系方式：</span>
              <div class="grow border-solid border-0 border-b border-gray-300 font-medium">{{ signatureContent.sign.phoneNumber }}</div>
            </div>
            <div class="flex items-center">
              <span class="text-gray-500">签约日期：</span>
              <div class="grow border-solid border-0 border-b border-gray-300 font-medium">{{ signatureContent.signDate }}</div>
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
</template>
