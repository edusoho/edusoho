<template>
  <!--    签署合同确认框-->
  <a-modal v-model:open="signContractConfirmVisible" :closable=false cancelText="取消" okText="去签署" width="416px"
           wrapClassName="to-sign-contract-modal" :onCancel="toCoursePage" :onOk="toSignContract">
    <div class="flex">
      <ExclamationCircleOutlined class="mr-16 w-22 h-22 text-[#FAAD14]" style="font-size: 22px"/>
      <div class="flex flex-col">
        <div class="text-16 text-[#1E2226] font-medium mb-8">签署电子合同</div>
        <div class="text-14 text-[#626973] font-normal">开始学习前请签署《合同名称》，以确保正常享受后续服务</div>
      </div>
    </div>
  </a-modal>

  <!--  签署合同页面-->
  <a-modal v-model:open="signContractVisible" :closable=false width="900px" wrapClassName="sign-contract-modal">
    <template #title>
      <div class="flex justify-between items-center px-24 py-16 border-b border-[#DCDEE0]">
        <div class="text-16 text-[#1E2226] font-medium">签署合同</div>
        <CloseOutlined class="h-16 w-16" @click="toCoursePage"/>
      </div>
    </template>
    <div class="p-24 flex">
      <div class="flex flex-1 mr-32 border border-solid border-[#DFE2E6] rounded-8 relative h-380">
        <div class="flex flex-col overflow-y-auto overscroll-auto pt-20 pb-73 w-full contract-detail-style">
          <div v-html="contractTemplate.content" class="text-12 text-[#626973] font-normal leading-20 mb-32"></div>
        </div>
        <div
          class="absolute bottom-0 z-10 flex justify-center items-center rounded-b-8 w-full py-16 border-t border-x-0 border-b-0 border-[#DFE2E6] border-solid hover:cursor-pointer bg-white"
          @click="showContractDetailModal">
          <img src="../../img/sign-contract/icon-01.jpg" class="w-16 h-16 mr-10" alt="">
          <div class="text-[#3DCD7F] text-14 font-normal">查看合同详情</div>
        </div>
      </div>
      <div class="flex flex-1">
        <a-form
          ref="form"
          :model="formState"
          name="basic"
          autocomplete="off"
          class="w-full"
        >
          <a-form-item
            label="乙方名称"
            :label-col="{ span: 8 }"
            :wrapper-col="{ span: 10 }"
            name="truename"
            :rules="[
                { required: true, message: '请输入乙方名称' },
                { pattern: /^[\u4e00-\u9fa5a-zA-Z]+$/, message: '只能输入汉字和英文' }
              ]"
          >
            <a-input v-model:value="formState.truename"/>
          </a-form-item>
          <a-form-item
            label="乙方身份证号"
            :label-col="{ span: 8 }"
            :wrapper-col="{ span: 16 }"
            name="IDNumber"
            v-if="contract.sign.IDNumber === 1"
            :rules="[
                { required: true, message: '请输入乙方身份证号' },
                { pattern: /^[1-9]\d{5}(18|19|20\d{2})(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)$/i, message: '身份证号不符合格式' }
              ]"
          >
            <a-input v-model:value="formState.IDNumber" class="w-full"/>
          </a-form-item>
          <a-form-item
            label="乙方联系方式"
            :label-col="{ span: 8 }"
            :wrapper-col="{ span: 12 }"
            name="phoneNumber"
            v-if="contract.sign.phoneNumber === 1"
            :rules="[
                { required: true, message: '请输入乙方联系方式' },
                { pattern: /^\d{1,11}$/, message: '请填写数字' }
              ]"
          >
            <a-input v-model:value="formState.phoneNumber" :maxlength="11"/>
          </a-form-item>
          <a-form-item
            label="手写签名"
            :label-col="{ span: 8 }"
            :wrapper-col="{ span: 12 }"
            name="handSignature"
            v-if="contract.sign.handSignature === 1"
            :rules="[
                { required: true, message: '请输入手写签名' },
              ]"
          >
            <div
              class="border-[#ebebeb] border-dashed border h-200 rounded-4 flex justify-center items-center mb-6 hover:cursor-pointer"
              @click="showSignModal">
              <div class="flex items-center" v-if="!formState.handSignature">
                <EditOutlined class="h-16 mr-8"/>
                <div class="text-[#1E2226] text-14 font-normal">开始签名</div>
              </div>
              <img v-if="formState.handSignature" class="w-160" :src="formState.handSignature" alt="">
              <div
                class="absolute bg-black/40 rounded-4 justify-center items-center h-200 w-full flex opacity-0 hover:opacity-100"
                v-if="formState.handSignature">
                <div class="bg-white px-15 py-6 flex border h-fit border-solid border-[#DFE2E6] rounded-6">
                  <EditOutlined class="h-16 mr-8"/>
                  <div class="text-[#1E2226] text-14 font-normal">重新签名</div>
                </div>
              </div>
            </div>

            <div class="text-12 text-[#8A9099] font-normal">请点击此区域进行手写签名</div>
          </a-form-item>

        </a-form>
      </div>
    </div>
    <template #footer>
      <div class="flex justify-center px-24 py-20">
        <a-button class="mr-8" @click="toCoursePage">关闭</a-button>
        <a-button type="primary" @click="submitContract"
                  :disabled="submitIsDisabled()">
          确认签署
        </a-button>
      </div>
    </template>
  </a-modal>

  <!--  合同详情页面-->
  <a-modal v-model:open="contractDetailVisible" width="100vw" :style="{ top: 0, height: '100%' }"
           wrapClassName="contract-detail-modal" :closable=false>
    <template #title>
      <div class="px-20 py-16 flex items-center">
        <div class="hover:cursor-pointer flex items-center" @click="contractDetailVisible = false;">
          <LeftOutlined class="h-14 mr-8"/>
          <div class="text-14 text-[#1E2226] font-normal">返回</div>
        </div>
        <a-divider type="vertical" class="mx-16"/>
        <div class="text-14 text-[#1E2226] font-normal mr-16">合同详情</div>
      </div>
    </template>
    <div v-html="contractTemplate.content" class="text-12 text-[#626973] font-normal leading-20"></div>
    <template #footer></template>
  </a-modal>

  <!--合同签字-->
  <a-modal v-model:open="signVisible" :closable=false cancelText="关闭" okText="确认签署" width="572px"
           wrapClassName="sign-contract-modal">
    <template #title>
      <div class="flex justify-between items-center px-24 py-16 border-b border-[#DCDEE0]">
        <div class="text-16 text-[#1E2226] font-medium">手写签名</div>
        <CloseOutlined class="h-16 w-16" @click="closeSignModal"/>
      </div>
    </template>
    <div class="p-24 flex flex-col">
      <div class="text-center text-14 text-[#37393D] font-normal mb-32">请确保“字迹清晰”并尽量把“签字范围”撑满</div>
      <div
        class="relative flex items-center justify-center border-[#86909C] border bg-center bg-no-repeat bg-[url('img/sign-contract/bg-01.jpg')] border-dashed rounded-8 h-256 w-full mb-8">
        <canvas id="canvas" class="rounded-8"></canvas>
      </div>
    </div>
    <template #footer>
      <div class="py-20 flex justify-center">
        <a-button class="mr-8" @click="clearSignature">清空</a-button>
        <a-button type="primary" @click="submitSignature">提交</a-button>
      </div>
    </template>
  </a-modal>
</template>
<script setup>
import {ref, reactive, nextTick, onMounted} from 'vue';
import {
  ExclamationCircleOutlined,
  CloseOutlined,
  LeftOutlined,
  EditOutlined,
} from '@ant-design/icons-vue';
import {message} from 'ant-design-vue';
import {SignContractApi} from '../../api/SignContract';
import SmoothSignature from 'smooth-signature';

const contractTemplate = ref();
const contract = ref();
const goodsKey = ref();

const signContractConfirmVisible = ref(true);
const signContractVisible = ref(false);
const contractDetailVisible = ref(false);
const signVisible = ref(false);

const courseId = ref();
const contractId = ref();
onMounted(() => {
  courseId.value = document.querySelector('input[name="course-id"]').value;
  contractId.value = document.querySelector('input[name="contract-id"]').value;
  goodsKey.value = document.querySelector('input[name="goods-key"]').value;
  console.log(goodsKey.value);
});

const showContractDetailModal = () => {
  contractDetailVisible.value = true;
};

const toSignContract = async () => {
  contractTemplate.value = await SignContractApi.getContractTemplate(contractId.value, goodsKey.value);
  console.log(contractTemplate.value);
  contract.value = await SignContractApi.getContract(contractId.value);
  console.log(contract.value);
  signContractConfirmVisible.value = false;
  signContractVisible.value = true;
};

const toCoursePage = () => {
  window.location.href = `/my/course/${courseId.value}`;
};

const signature = ref();

const showSignModal = async () => {
  signVisible.value = true;
  signContractVisible.value = false;
  await nextTick();
  const canvas = document.getElementById('canvas');
  signature.value = new SmoothSignature(canvas, {
    width: 524,
    height: 256,
  });
};

const closeSignModal = () => {
  signVisible.value = false;
  signContractVisible.value = true;
};

const formState = reactive({
  truename: '',
  IDNumber: '',
  phoneNumber: '',
  handSignature: '',
});

const submitSignature = () => {
  if (!signature.value.isEmpty()) {
    formState.handSignature = signature.value.getPNG();
    signVisible.value = false;
    signContractVisible.value = true;

  } else {
    message.error('请输入手写签名');
  }
};

const clearSignature = () => {
  signature.value.clear();
};

const params = ref();

const submitContract = async () => {
  const baseParams = {
    contractCode: contractTemplate.value.code,
    goodsKey: goodsKey.value,
    truename: formState.truename,
  };
  const optionalFields = {
    IDNumber: contract.value.sign.IDNumber === 1 ? formState.IDNumber : undefined,
    phoneNumber: contract.value.sign.phoneNumber === 1 ? formState.phoneNumber : undefined,
    handSignature: contract.value.sign.handSignature === 1 ? formState.handSignature : undefined,
  };
  const params = {...baseParams, ...Object.fromEntries(Object.entries(optionalFields).filter(([_, v]) => v !== undefined))};
  await SignContractApi.signContract(contractId.value, params);
  signContractVisible.value = false;
};

const submitIsDisabled = () => {
  const {IDNumber, phoneNumber, handSignature} = contract.value.sign;
  const fieldsToCheck = [
    {key: 'IDNumber', value: IDNumber},
    {key: 'phoneNumber', value: phoneNumber},
    {key: 'handSignature', value: handSignature}
  ];
  return !fieldsToCheck.every(({key, value}) =>
    value === 0 || formState[key] !== undefined && formState[key] !== ''
  );
};
</script>


<style lang="less">
.to-sign-contract-modal {
  .ant-modal-content {
    padding: 32px 32px 24px 32px;
  }

  .ant-modal-body {
    padding: 0;
  }

  .ant-modal-footer {
    padding: 0;
    border: none;
    border-radius: 0;
    margin-top: 24px;
  }

  .ant-btn-primary:hover {
    background-color: #BDF2D0;
    border-color: #BDF2D0;
  }

  .ant-btn-default:hover {
    border-color: #46C37B;
    color: #46C37B;
  }
}

.sign-contract-modal {
  .ant-modal-header {
    padding: 0;
    margin-bottom: 0;
  }

  .ant-modal-content {
    padding: 0;
  }

  .ant-modal-body {
    padding: 0;
  }

  .ant-modal-footer {
    padding: 0;
    margin-top: 0;
  }

  .ant-btn-primary:hover {
    background-color: #BDF2D0;
    border-color: #BDF2D0;
  }

  .ant-btn-default:hover {
    border-color: #46C37B;
    color: #46C37B;
  }

  .ant-form-item-label {
    .ant-form-item-required {
      color: #626973;
      font-size: 14px;
      line-height: 22px;
      font-weight: 400;
      margin-right: 10px;
    }
  }

  .ant-form-item:last-child {
    margin-bottom: 0;
  }

  .ant-btn[disabled] {
    background-color: #BDF2D0;
    border: 1px #BDF2D0;
    color: #F0FFF4;
  }
}

.contract-detail-style {
  padding-left: 32px;
  padding-right: 32px;
}

.contract-detail-modal {
  .ant-modal-content {
    padding: 0;
    border-radius: 0;
  }

  .ant-modal-body {
    padding-top: 24px;
    padding-bottom: 24px;
    margin-left: auto;
    margin-right: auto;
    width: 960px;
  }

  .ant-modal-footer {
    padding: 0;
    border: none;
    border-radius: 0;
    margin-top: 24px;
  }

  .ant-btn-primary:hover {
    background-color: #BDF2D0;
    border-color: #BDF2D0;
  }

  .ant-btn-default:hover {
    border-color: #46C37B;
    color: #46C37B;
  }

  .ant-modal {
    max-width: 100%;
  }

  .ant-modal-header {
    padding: 0;
    margin-bottom: 0;
  }
}

.ant-message {
  left: 50%;
  transform: translateX(-50%)
}

</style>
