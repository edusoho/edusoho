<script setup>
import {reactive, ref} from 'vue';
import {Empty, message} from 'ant-design-vue';
import { t } from './vue-lang';
const simpleImage = Empty.PRESENTED_IMAGE_SIMPLE;
import AntConfigProvider from '../../../components/AntConfigProvider.vue';
import {CloseOutlined} from '@ant-design/icons-vue';
import Api from '../../../../api';

message.config({
  top: `90px`,
});

const pagination = reactive({
  current: 1,
  total: 0,
  pageSize: 10,
});
const contracts = ref([]);

function getTableTotal(total) {
  return `${ t('pagination.total') } ${ total } ${ t('pagination.item') }`;
}

async function fetchMyContracts(params) {
  const {data, paging} = await Api.contract.getMyContracts(params);
  pagination.total = Number(paging.total);
  pagination.pageSize = Number(paging.limit);
  contracts.value = data;
}

async function handleTableChange(paging) {
  pagination.current = paging.current === 0 ? 1 : paging.current;
  pagination.total = paging.total;
  pagination.pageSize = paging.pageSize;
  const params = {
    limit: paging.pageSize,
    offset: (paging.current - 1) * paging.pageSize,
  };
  await fetchMyContracts(params);
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
  await fetchMyContracts(params);
};
getList();

const signatureContent = ref();
const myContentVisible = ref(false);
const courseName = ref();
const view = async (id, name) => {
  signatureContent.value = await Api.contract.getSignedContract(id);
  courseName.value = name;
  myContentVisible.value = true;
};

const downloadContract = async (id, fileName) => {
  try {
    message.loading(`${ t('message.downloading') }...`, 0);
    const response = await Api.contract.downloadContract(id, 'blob');

    const url = window.URL.createObjectURL(response);
    const a = document.createElement('a');
    a.href = url;
    a.download = fileName;
    document.body.appendChild(a);
    message.destroy();
    a.click();

    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
  } catch (error) {
    message.destroy();
    message.error(`${ t('message.contractDownloadFailure') }`);
  }
}
</script>

<template>
  <ant-config-provider>
    <div class="w-full h-fit bg-white rounded-4 border border-[#F0F0F0] border-solid pt-24 px-24">
      <div class="text-[#1E2226] text-18 font-medium w-full border border-[#F0F0F0] pb-16 border-x-0 border-t-0 border-solid">{{ t('title') }}</div>
      <div class="w-full h-full flex-col">
        <div v-if="contracts.length !== 0" v-for="contract in contracts"
             class="flex justify-between items-center px-16 py-36 border border-[#F0F0F0] border-x-0 border-t-0 border-solid">
          <div class="flex">
            <img class="w-45 ml-11 mr-24" src="../../../../img/my-contract/icon-01.jpg" alt="">
            <div class="flex flex-col">
              <div
                class="w-320 overflow-hidden text-ellipsis whitespace-nowrap mb-12 text-16 text-[#37393D] font-medium">
                {{ contract.name }}
              </div>
              <div class="text-12 text-[#919399] font-normal"><span
                v-if="contract.relatedGoods.type === 'course'">{{ `${ t('associatedCurriculum') }：` }}</span><span
                v-if="contract.relatedGoods.type === 'classroom'">{{ `${ t('associatedClass') }：` }}</span><span
                v-if="contract.relatedGoods.type === 'itemBankExercise'">{{ `${ t('relatedQuestionBank') }：` }}</span>{{
                  contract.relatedGoods.name
                }}
              </div>
            </div>
          </div>
          <div class="my-contract-btn space-x-16">
            <a-button @click="downloadContract(contract.id, `${contract.relatedGoods.name}-${contract.name}`)">{{ t('btn.download') }}</a-button>
            <a-button type="primary" @click="view(contract.id, contract.relatedGoods.name)">{{ t('btn.view') }}</a-button>
          </div>
        </div>
        <div v-else class="border border-[#F0F0F0] border-x-0 border-t-0 border-solid">
          <a-empty :image="simpleImage" :description="t('message.noContract')"/>
        </div>
      </div>
      <a-modal :width="900"
               v-model:open="myContentVisible"
               :closable=false
               :zIndex="1050"
               :centered="true"
               :bodyStyle="{ 'height': '563px', 'overflow': 'auto'}"
               wrapClassName="my-contract-detail-modal"
      >
        <template #title>
          <div class="flex justify-between items-center px-24 py-16 border border-solid border-[#F0F0F0] border-t-0 border-x-0">
            <div class="text-16 text-[#1E2226] font-medium">{{ `${courseName}-${ t('modal.contractSigning') }` }}</div>
            <CloseOutlined class="h-16 w-16" @click="myContentVisible = false"/>
          </div>
        </template>
        <div class="w-full flex flex-col space-y-32 p-32">
          <div class="flex items-end justify-between gap-4">
            <span class="flex-none whitespace-nowrap opacity-0">{{ `${ t('modal.contractNumber') }: ${signatureContent.code}` }}</span>
            <span class="grow text-center text-22 font-medium">{{ signatureContent.name }}</span>
            <span class="flex-none whitespace-nowrap text-gray-500">{{ `${ t('modal.contractNumber') }: ${signatureContent.code}` }}</span>
          </div>
          <div v-html="signatureContent.content" class="text-gray-500 contract-content"></div>
          <div class="flex space-x-64">
            <div class="flex-1 flex flex-col items-start justify-between space-y-22">
              <span class="text-18 font-medium">{{ `${ t('modal.partyA') }：` }}</span>
              <div class="w-full flex flex-col space-y-22">
                <img :src="signatureContent.seal" alt="" class="w-150 h-150"/>
                <div class="flex items-center">
                  <span class="text-gray-500">{{ `${ t('modal.signingDate') }：` }}</span>
                  <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                    {{ signatureContent.signDate }}
                  </div>
                </div>
              </div>
            </div>
            <div class="flex-1 flex flex-col items-start justify-between">
              <span class="text-18 font-medium">{{ `${ t('modal.partyB') }：` }}</span>
              <div class="w-full flex flex-col space-y-22">
                <div v-if="signatureContent.sign && signatureContent.sign.handSignature" class="flex items-center">
                  <span class="text-gray-500">{{ `${ t('modal.handSignature') }：` }}</span>
                  <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                    <img :src="signatureContent.sign.handSignature" class="h-35" alt=""/>
                  </div>
                </div>
                <div v-if="signatureContent.sign && signatureContent.sign.truename" class="flex items-center">
                  <span class="text-gray-500">{{ `${ t('modal.partyBName') }：` }}</span>
                  <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                    {{ signatureContent.sign.truename }}
                  </div>
                </div>
                <div v-if="signatureContent.sign && signatureContent.sign.IDNumber" class="flex items-center">
                  <span class="text-gray-500">{{ `${ t('modal.iDNumber') }：` }}</span>
                  <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                    {{ signatureContent.sign.IDNumber }}
                  </div>
                </div>
                <div v-if="signatureContent.sign && signatureContent.sign.phoneNumber" class="flex items-center">
                  <span class="text-gray-500">{{ `${ t('modal.contactInformation') }：` }}</span>
                  <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                    {{ signatureContent.sign.phoneNumber }}
                  </div>
                </div>
                <div class="flex items-center">
                  <span class="text-gray-500">{{ `${ t('modal.signingDate') }：` }}</span>
                  <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                    {{ signatureContent.signDate }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <template #footer>
          <div class="flex justify-center py-10 border border-solid border-[#F0F0F0] border-b-0 border-x-0">
            <a-button @click="myContentVisible = false; courseName = ''">{{ t('btn.close') }}</a-button>
          </div>
        </template>
      </a-modal>
      <div
        class="w-full bg-white px-40 py-24 flex justify-end">
        <a-pagination
          :show-total="total => getTableTotal(total)"
          v-model="pagination.current"
          :total="pagination.total"
          @change="handlePaginationChange"
          show-less-items
          :disabled="pagination.total === 0"
        />
      </div>
    </div>
  </ant-config-provider>
</template>

<style lang="less">
.my-contract-detail-modal {
  .ant-modal {
    .ant-modal-content {
      padding: 0;
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
