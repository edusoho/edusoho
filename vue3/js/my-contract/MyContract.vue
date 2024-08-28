<template>
  <es-config-provider>
    <div class="w-full h-[555px] bg-white rounded-4 border border-[#e4ecf3] border-solid p-24 relative">
      <div class="text-[#1E2226] text-18 font-medium">我的合同</div>
      <div class="w-full overflow-y-auto overscroll-none flex-col pb-63" style="height: calc(100% - 20px);">
        <div v-if="contracts.length !== 0" v-for="contract in contracts"
             class="flex justify-between items-center px-16 py-36  border border-[#e4ecf3] border-x-0 border-t-0 border-solid">
          <div class="flex">
            <img class="w-45 ml-11 mr-24" src="../../img/my-contract/icon-01.jpg" alt="">
            <div class="flex flex-col">
              <div
                class="w-320 overflow-hidden text-ellipsis whitespace-nowrap mb-12 text-16 text-[#37393D] font-medium">
                {{ contract.name }}
              </div>
              <div class="text-12 text-[#919399] font-normal"><span
                v-if="contract.relatedGoods.type === 'course'">关联课程：</span><span
                v-if="contract.relatedGoods.type === 'classroom'">关联班级：</span><span
                v-if="contract.relatedGoods.type === 'itemBankExercise'">关联题库：</span>{{ contract.relatedGoods.name }}
              </div>
            </div>
          </div>
          <div class="my-contract-btn">
            <a-button type="primary" @click="view(contract.id, contract.relatedGoods.name)">查看</a-button>
          </div>
        </div>
        <div v-else>
          <a-empty :image="simpleImage" description="暂无合同"/>
        </div>
      </div>
      <a-modal :width="900"
               v-model:open="signatureContentVisible"
               :title="`${courseName}-电子合同签署`"
               :bodyStyle="{'height': 'fit-content', 'max-height': '500px', 'overflow': 'auto'}"
               wrapClassName="my-contract-detail-modal"
      >
        <div class="w-full flex flex-col space-y-32 p-32">
          <div class="flex items-end justify-between gap-4">
            <span class="flex-none whitespace-nowrap opacity-0">{{ `合同编号: ${signatureContent.code}` }}</span>
            <span class="grow text-center text-22 font-medium">{{ signatureContent.name }}</span>
            <span class="flex-none whitespace-nowrap text-gray-500">{{ `合同编号: ${signatureContent.code}` }}</span>
          </div>
          <div v-html="signatureContent.content" class="text-gray-500 contract-content"></div>
          <div class="flex space-x-64">
            <div class="flex-1 flex flex-col items-start justify-between space-y-22">
              <span class="text-18 font-medium">甲方：</span>
              <div class="w-full flex flex-col space-y-22">
                <img :src="signatureContent.seal" alt="甲方印章" class="w-150 h-150"/>
                <div class="flex items-center">
                  <span class="text-gray-500">签约日期：</span>
                  <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                    {{ signatureContent.signDate }}
                  </div>
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
                  <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                    {{ signatureContent.sign.truename }}
                  </div>
                </div>
                <div v-if="signatureContent.sign && signatureContent.sign.IDNumber" class="flex items-center">
                  <span class="text-gray-500">身份证号：</span>
                  <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                    {{ signatureContent.sign.IDNumber }}
                  </div>
                </div>
                <div v-if="signatureContent.sign && signatureContent.sign.phoneNumber" class="flex items-center">
                  <span class="text-gray-500">联系方式：</span>
                  <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                    {{ signatureContent.sign.phoneNumber }}
                  </div>
                </div>
                <div class="flex items-center">
                  <span class="text-gray-500">签约日期：</span>
                  <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                    {{ signatureContent.signDate }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <template #footer>
          <div class="flex justify-center">
            <a-button @click="signatureContentVisible = false; courseName = ''">关闭</a-button>
          </div>
        </template>
      </a-modal>
      <div
        class="absolute w-full left-0 bottom-0 z-10 bg-white px-40 py-24 border border-x-0 border-b-0 border-solid border-[#e4ecf3] flex justify-end">
        <a-pagination
          class="my-contract-pagination"
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
  </es-config-provider>
</template>

<script setup>
import {reactive, ref} from 'vue';
import {MyContractApi} from '../../api/MyContract';
import {Empty} from 'ant-design-vue';

const simpleImage = Empty.PRESENTED_IMAGE_SIMPLE;
import EsConfigProvider from '../components/EsConfigProvider.vue';


const pagination = reactive({
  current: 1,
  total: 0,
  pageSize: 10,
});
const pageSizeOptions = ['10', '20', '30', '40', '50'];
const contracts = ref([]);

function getTableTotal(total) {
  return `共 ${total} 项`;
}

async function fetchMyContracts(params) {
  const {data, paging} = await MyContractApi.getMyContracts(params);
  pagination.total = Number(paging.total);
  pagination.pageSize = Number(paging.limit);
  contracts.value = data;
}

async function handleTableChange(paging) {
  pagination.current = paging.current;
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
const signatureContentVisible = ref(false);
const courseName = ref();
const view = async (id, name) => {
  signatureContent.value = await MyContractApi.getSignedContract(id);
  courseName.value = name;
  signatureContentVisible.value = true;
};

</script>

<style lang="less">
.my-contract-btn {
  .ant-btn-primary:hover {
    background-color: #BDF2D0;
    border-color: #BDF2D0;
  }
}

.contract-content {
  img {
    max-width: 100%;
  }
}


.my-contract-detail-modal {
  .ant-modal-header {
    padding: 24px;
    margin-bottom: 0;
    border: none;
  }

  .ant-modal-content {
    padding: 0;
  }

  .ant-modal-body {
    padding: 0;
  }

  .ant-modal-footer {
    padding: 16px;
    border: none;
  }

  .ant-modal-close-x {
    height: 24px;
    width: 24px;
  }
}

.my-contract-pagination {
  .ant-pagination-item-active {
    border-color: #46C37B;

    a {
      color: #46C37B;
    }
  }

  //.ant-pagination-options {
  //  .ant-select-selector:hover {
  //    border-color: #46C37B !important;
  //  }
  //  .ant-pagination-options-quick-jumper input:hover {
  //    border-color: #46C37B !important;
  //  }
  //  .ant-pagination-options-quick-jumper input:focus {
  //    border-color: #46C37B !important;
  //  }
  //}
}
</style>
