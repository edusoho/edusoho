<script setup>
import {createVNode, reactive, ref} from 'vue';
import {message, Modal} from 'ant-design-vue';
import {useRouter} from 'vue-router';
import {CloseOutlined, ExclamationCircleOutlined} from '@ant-design/icons-vue';
import {t} from './vue-lang';
import Api from '../../api';
import {formatDate} from 'vue3/js/common';

const contractManagementColumns = [
  {
    key: 'name',
    title: `${t('list.title.name')}`,
    dataIndex: 'name',
    width: 400,
    ellipsis: true,
  },
  {
    key: 'relatedGoods',
    title: `${t('list.title.relatedGoods')}`,
    dataIndex: 'relatedGoods',
    width: 200,
  },
  {
    key: 'updatedUser',
    title: `${t('list.title.regenerator')}`,
    dataIndex: 'updatedUser.nickname',
    width: 150,
  },
  {
    key: 'updatedTime',
    title: `${t('list.title.updateTime')}`,
    width: 210,
  },
  {
    key: 'operation',
    title: `${t('list.title.controls')}`,
    width: 200,
    fixed: 'right',
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
  const {data, paging} = await Api.contract.search(searchQuery);
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
  return `${t('pagination.total')} ${total} ${t('pagination.item')}`;
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

function showDeleteConfirm(id, name) {
  Modal.confirm({
    title: `${t('modal.title.confirmDelete')}《${name}》？`,
    icon: createVNode(ExclamationCircleOutlined),
    content: `${t('modal.cannotBeRestored')}...`,
    centered: true,
    okText: `${t('btn.delete')}`,
    async onOk() {
      await Api.contract.delete(id);
      message.success(t('message.successfullyDelete'));
      await getList();
    },
    onCancel() {
    },
  });
}

const contractContent = ref();
const contractContentVisible = ref(false);
const selectedSignatureContract = ref({});
const view = async (record) => {
  selectedSignatureContract.value = record;
  contractContent.value = await Api.contract.getContractWithHtml(record.id);
  contractContentVisible.value = true;
};

const router = useRouter();
const toUpdateContract = (id) => {
  router.push({name: 'EditContract', query: {contractId: id, editType: 'update'}});
};
</script>

<template>

  <div class="flex flex-col space-y-24">
    <div class="space-x-20">
      <a-select v-model:value="keywordType" style="width: 140px" :placeholder="t('placeholder.searchType')" allow-clear>
        <a-select-option value="name">{{ t('select.name') }}</a-select-option>
        <a-select-option value="username">{{ t('select.regenerator') }}</a-select-option>
      </a-select>
      <a-input v-model:value="keyword" :placeholder="t('placeholder.enterName')" style="width: 360px"></a-input>
      <a-button type="primary" ghost @click="onSearch">{{ t('btn.search') }}</a-button>
      <a-button @click="onReset">{{ t('btn.reset') }}</a-button>
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
        <template v-if="column.key === 'name'">
          <div class="flex flex-col items-start">
            <a-tooltip placement="topLeft" :overlayStyle="{ maxWidth: '450px', whiteSpace: 'normal' }">
              <template #title class="w-400">{{ record.name }}</template>
              <div class="w-full overflow-hidden text-ellipsis whitespace-nowrap">{{ record.name }}</div>
            </a-tooltip>
          </div>
        </template>
        <template v-if="column.key === 'relatedGoods'">
          <div class="flex flex-col items-start">
            <span><span class="text-[#8A9099]">{{
                t('list.content.curriculum')
              }}：</span>{{ `${record.relatedGoodsCount ? record.relatedGoodsCount.course ?? 0 : 0}` }}</span>
            <span><span class="text-[#8A9099]">{{
                t('list.content.class')
              }}：</span>{{ `${record.relatedGoodsCount ? record.relatedGoodsCount.classroom ?? 0 : 0}` }}</span>
            <span><span class="text-[#8A9099]">{{
                t('list.content.questionBank')
              }}：</span>{{ `${record.relatedGoodsCount ? record.relatedGoodsCount.itemBankExercise ?? 0 : 0}` }}</span>
          </div>
        </template>
        <template v-else-if="column.key === 'operation'">
          <div class="flex contract-list-operation-btn space-x-16">
            <a-button type="link" @click="view(record)">{{ t('btn.view') }}</a-button>
            <a-button type="link" @click="toUpdateContract(record.id)">{{ t('btn.editor') }}</a-button>
            <a-button type="link" @click="showDeleteConfirm(record.id, record.name)">{{ t('btn.delete') }}</a-button>
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
             v-model:open="contractContentVisible"
             :closable=false
             :centered="true"
             zIndex="1050"
             wrapClassName="contract-list-detail-modal"
             :bodyStyle="{ 'height': '563px', 'overflow': 'auto'}"
    >
      <template #title>
        <div class="flex justify-between items-center px-24 py-16 border-solid border-[#F0F0F0] border-t-0 border-x-0">
          <div class="text-16 text-[#1E2226] font-medium">{{ t('modal.contractSigning') }}</div>
          <CloseOutlined class="h-16 w-16" @click="contractContentVisible = false"/>
        </div>
      </template>
      <div class="w-full flex flex-col space-y-32 p-32">
        <div class="flex items-end justify-between gap-4">
          <span class="flex-none whitespace-nowrap opacity-0 mr-100">{{ `${t('modal.contractNumber')}：` }}</span>
          <span class="grow text-center text-22 font-medium">{{ contractContent.name }}</span>
          <span class="flex-none whitespace-nowrap text-gray-500 mr-100">{{ `${t('modal.contractNumber')}：` }}</span>
        </div>
        <div v-html="contractContent.content" class="text-gray-500 contract-content"></div>
        <div class="flex space-x-64">
          <div class="flex-1 flex flex-col items-start justify-between space-y-22">
            <span class="text-18 font-medium">{{ `${t('modal.partyA')}：` }}</span>
            <div class="w-full flex flex-col space-y-22">
              <img :src="contractContent.seal" alt="甲方印章" class="w-150 h-150"/>
              <div class="flex items-center">
                <span class="text-gray-500">{{ `${t('modal.signingDate')}：` }}</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium mt-20"></div>
              </div>
            </div>
          </div>
          <div class="flex-1 flex flex-col items-start justify-between">
            <span class="text-18 font-medium">{{ `${t('modal.partyB')}：` }}</span>
            <div class="w-full flex flex-col space-y-22">
              <div v-if="contractContent.sign && contractContent.sign.handSignature" class="flex items-center">
                <span class="text-gray-500">{{ `${t('modal.handSignature')}：` }}</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium mt-20"></div>
              </div>
              <div class="flex items-center">
                <span class="text-gray-500">{{ `${t('modal.partyBName')}：` }}</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium mt-20"></div>
              </div>
              <div v-if="contractContent.sign && contractContent.sign.IDNumber" class="flex items-center">
                <span class="text-gray-500">{{ `${t('modal.iDNumber')}：` }}</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium mt-20"></div>
              </div>
              <div v-if="contractContent.sign && contractContent.sign.phoneNumber" class="flex items-center">
                <span class="text-gray-500">{{ `${t('modal.contactInformation')}：` }}</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium mt-20"></div>
              </div>
              <div class="flex items-center">
                <span class="text-gray-500">{{ `${t('modal.signingDate')}：` }}</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium mt-20"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <template #footer>
        <div class="flex justify-center border-solid p-16 border-[#F0F0F0] border-b-0 border-x-0">
          <a-button @click="contractContentVisible = false">{{ t('btn.close') }}</a-button>
        </div>
      </template>
    </a-modal>
  </div>
  <!--  <contract-drawer v-model:visible="drawerVisible" :type="drawerType"/>-->
</template>
<style lang="less">
.contract-list-operation-btn {
  .ant-btn {
    padding: 0;
  }
}

.contract-list-detail-modal {
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

<style lang="less" scoped>
.contract-content {
  /deep/ img {
    max-width: 100%;
  }
}
</style>
