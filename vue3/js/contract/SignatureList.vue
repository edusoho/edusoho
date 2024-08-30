<script setup>
import {reactive, ref, watch} from 'vue';
import {ContractApi} from '../../api/Contract.js';
import {CloseOutlined, InfoCircleOutlined} from '@ant-design/icons-vue';
import {formatDate} from '../common';
import { t } from './vue-lang';

const dateFormat = 'YYYY-MM-DD';

const columns = [
  {
    key: 'contractCode',
    title: `${ t('list.title.contractNumber') }`,
    dataIndex: 'contractCode',
    width: 170,
  },
  {
    key: 'username',
    title: `${ t('list.title.username') }`,
    dataIndex: 'username',
    align: 'center',
    width: 200,
  },
  {
    key: 'mobile',
    title: `${ t('list.title.phoneNumber') }`,
    dataIndex: 'mobile',
    width: 150,
  },
  {
    key: 'goodsType',
    title: `${ t('list.title.commodityType') }`,
    dataIndex: 'goodsType',
    width: 100,
  },
  {
    key: 'goodsName',
    title: `${ t('list.title.tradeName') }`,
    dataIndex: 'goodsName',
    width: 250,
    ellipsis: true,
  },
  {
    key: 'contractName',
    title: `${ t('list.title.contractName') }`,
    dataIndex: 'contractName',
    width: 250,
  },
  {
    key: 'operation',
    title: `${ t('list.title.controls') }`,
    width: 90,
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
  signTime.value = undefined;
  goodsType.value = undefined;
  const params = {
    limit: pagination.pageSize,
    offset: (pagination.current - 1) * pagination.pageSize,
  };
  await fetchContracts(params);
}

function getTableTotal(total) {
  return `${ t('pagination.total') } ${ total } ${ t('pagination.item') }`;
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
      <a-select v-model:value="goodsType" style="width: 100px" :placeholder="t('placeholder.commodityType')" allow-clear>
        <a-select-option value="course">{{ t('select.curriculum') }}</a-select-option>
        <a-select-option value="itemBankExercise">{{ t('select.questionBank') }}</a-select-option>
        <a-select-option value="classroom">{{ t('select.class') }}</a-select-option>
      </a-select>
      <div class="flex items-center">
        <span>{{ t('label.signatureTime') }}：</span>
        <a-range-picker v-model:value="signTime" />
      </div>
      <a-select v-model:value="keywordType" style="width: 100px">
        <a-select-option value="username">{{ t('select.username') }}</a-select-option>
        <a-select-option value="mobile">{{ t('select.phoneNumber') }}</a-select-option>
        <a-select-option value="goodsName">{{ t('select.tradeName') }}</a-select-option>
      </a-select>
      <a-input v-model:value="keyword" :placeholder="t('placeholder.enterName')" style="width: 360px"></a-input>
      <a-button type="primary" ghost @click="onSearch">{{ t('btn.search') }}</a-button>
      <a-button @click="onReset">{{ t('btn.reset') }}</a-button>
    </div>
    <a-table
      :columns="columns"
      :data-source="pageData"
      :row-key="record => record.id"
      :pagination="false"
      :loading="loading"
      :scroll="{ x: 1500 }"
      @change="handleTableChange"
    >
      <template #headerCell="{ column }">
        <template v-if="column.key === 'goodsName'">
          <span>
            {{ t('list.title.tradeName') }}
            <a-tooltip placement="topLeft" :title="t('tip.title')">
              <info-circle-outlined />
            </a-tooltip>
          </span>
        </template>
      </template>
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'goodsName'">
          {{ record.goodsName }}
          <br>
          <span class="text-[#8A9099] text-12">{{ `${ t('list.content.orderNumber') }：${record.orderSn ? record.orderSn : '-'}` }}</span>
        </template>
        <template v-else-if="column.key === 'contractName'">
          {{ record.contractName }}
          <br>
          <span class="text-[#8A9099] text-12">{{ `${ t('content.signatureTime') }：${ formatDate(record.signTime) }` }}</span>
        </template>
        <template v-else-if="column.key === 'operation'">
          <div class="signature-list-operation-btn">
            <a-button type="link" @click="view(record)">{{ t('btn.view') }}</a-button>
          </div>
        </template>
        <template v-else-if="column.key === 'mobile'">
          {{ record.mobile ? record.mobile : '-' }}
        </template>
        <template v-else-if="column.key === 'goodsType'">
          {{
            record.goodsType === 'course' ? `${ t('list.content.curriculum') }` : record.goodsType === 'classroom' ? `${ t('list.content.class') }` : record.goodsType === 'itemBankExercise' ? `${ t('list.content.questionBank') }` : record.goodsType
          }}
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
           wrapClassName="signature-list-detail-modal"
           v-model:open="signatureContentVisible"
           :closable=false
           :centered="true"
           :bodyStyle="{ 'height': '563px', 'overflow': 'auto'}"
  >
    <template #title>
      <div class="flex justify-between items-center px-24 py-16 border-solid border-[#F0F0F0] border-t-0 border-x-0">
        <div class="text-16 text-[#1E2226] font-medium">{{ `${ selectedSignatureContract.goodsName }-${ selectedSignatureContract.username }-${ t('modal.contractSigning') }` }}</div>
        <CloseOutlined class="h-16 w-16" @click="signatureContentVisible = false"/>
      </div>
    </template>
    <div class="w-full flex flex-col space-y-32 p-32">
      <div class="flex items-end justify-between gap-4">
        <span class="flex-none whitespace-nowrap opacity-0">{{ `${ t('modal.contractNumber') }: ${ signatureContent.code }` }}</span>
        <span class="grow text-center text-22 font-medium">{{ signatureContent.name }}</span>
        <span class="flex-none whitespace-nowrap text-gray-500">{{ `${ t('modal.contractNumber') }: ${ signatureContent.code }` }}</span>
      </div>
      <div v-html="signatureContent.content" class="text-gray-500"></div>
      <div class="flex space-x-64">
        <div class="flex-1 flex flex-col items-start justify-between space-y-22">
          <span class="text-18 font-medium">{{ `${ t('modal.partyA') }：` }}</span>
          <div class="w-full flex flex-col space-y-22">
            <img :src="signatureContent.seal" alt="" class="w-150 h-150" />
            <div class="flex items-center">
              <span class="text-gray-500">{{ `${ t('modal.signingDate') }：` }}</span>
              <div class="grow border-solid border-0 border-b border-gray-300 font-medium">{{ signatureContent.signDate }}</div>
            </div>
          </div>
        </div>
        <div class="flex-1 flex flex-col items-start justify-between">
          <span class="text-18 font-medium">{{ `${ t('modal.partyB') }：` }}</span>
          <div class="w-full flex flex-col space-y-22">
            <div v-if="signatureContent.sign && signatureContent.sign.handSignature" class="flex items-center">
              <span class="text-gray-500">{{ `${ t('modal.handSignature') }：` }}</span>
              <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                <img :src="signatureContent.sign.handSignature" class="h-35" alt="手写签名"/>
              </div>
            </div>
            <div v-if="signatureContent.sign && signatureContent.sign.truename" class="flex items-center">
              <span class="text-gray-500">{{ `${ t('modal.partyBName') }：` }}</span>
              <div class="grow border-solid border-0 border-b border-gray-300 font-medium">{{ signatureContent.sign.truename }}</div>
            </div>
            <div v-if="signatureContent.sign && signatureContent.sign.IDNumber" class="flex items-center">
              <span class="text-gray-500">{{ `${ t('modal.iDNumber') }：` }}</span>
              <div class="grow border-solid border-0 border-b border-gray-300 font-medium">{{ signatureContent.sign.IDNumber }}</div>
            </div>
            <div v-if="signatureContent.sign && signatureContent.sign.phoneNumber" class="flex items-center">
              <span class="text-gray-500">{{ `${ t('modal.contactInformation') }：` }}</span>
              <div class="grow border-solid border-0 border-b border-gray-300 font-medium">{{ signatureContent.sign.phoneNumber }}</div>
            </div>
            <div class="flex items-center">
              <span class="text-gray-500">{{ `${ t('modal.signingDate') }：` }}</span>
              <div class="grow border-solid border-0 border-b border-gray-300 font-medium">{{ signatureContent.signDate }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <template #footer>
      <div class="flex justify-center p-16 border-solid border-[#F0F0F0] border-b-0 border-x-0">
        <a-button @click="signatureContentVisible = false">{{ t('btn.close') }}</a-button>
      </div>
    </template>
  </a-modal>
</template>
<style lang="less">
.signature-list-operation-btn {
  .ant-btn {
    padding: 0;
  }
}

.signature-list-detail-modal {
  .ant-modal {
    padding: 0 !important;
    .ant-modal-content {
      padding: 0 !important;
      .ant-modal-footer {
        margin-top: 0;
      }
      .ant-modal-header {
        margin-bottom: 0;
      }
    }
  }
}
</style>
