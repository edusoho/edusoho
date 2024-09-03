<script setup>
import {ref, reactive, nextTick, onMounted, computed} from 'vue';
import {
  ExclamationCircleOutlined,
  CloseOutlined,
  LeftOutlined,
  EditOutlined,
} from '@ant-design/icons-vue';
import {message} from 'ant-design-vue';
import SmoothSignature from 'smooth-signature';
import {t} from './vue-lang';
import Api from '../../api';
import {useI18n} from 'vue-i18n';

const contractTemplate = ref();
const contract = ref();
const goodsKey = ref();

const signContractConfirmVisible = ref(false);
const signContractVisible = ref(false);
const contractDetailVisible = ref(false);
const signVisible = ref(false);

const courseId = ref();
const exerciseId = ref();
const moduleId = ref();
const contractId = ref();
const pathname = ref();
const sign = ref();
const targetTitle = ref();
const nickname = ref();

const formState = reactive({
  truename: '',
  IDNumber: '',
  phoneNumber: '',
  handSignature: '',
});
onMounted(async () => {
  goodsKey.value = document.querySelector('input[name="goods-key"]').value;
  if (goodsKey.value.includes('itemBankExercise')) {
    const path = window.location.pathname;
    const parts = path.split('/');
    exerciseId.value = parts[2];
    moduleId.value = parts[4];
    pathname.value = parts[5];
  } else {
    courseId.value = document.querySelector('input[name="course-id"]').value;
  }
  contractId.value = document.querySelector('input[name="contract-id"]').value;
  sign.value = document.querySelector('input[name="sign"]').value;
  targetTitle.value = document.querySelector('input[name="target-title"]').value;
  nickname.value = document.querySelector('input[name="nickname"]').value;
  contractTemplate.value = await Api.contract.getContractSignTemplate(contractId.value, goodsKey.value);
  signContractConfirmVisible.value = true;
  formState.truename = contractTemplate.value.signFields.find(item => item.field === 'truename')?.default;
  formState.IDNumber = contractTemplate.value.signFields.find(item => item.field === 'IDNumber')?.default;
  formState.phoneNumber = contractTemplate.value.signFields.find(item => item.field === 'phoneNumber')?.default;
});

const showContractDetailModal = () => {
  contractDetailVisible.value = true;
};

const toSignContract = async () => {
  contract.value = await Api.contract.get(contractId.value);
  signContractConfirmVisible.value = false;
  signContractVisible.value = true;
};

const toCoursePage = () => {
  if (sign.value === 'required') {
    if (goodsKey.value.includes('itemBankExercise')) {
      window.location.href = `/my/item_bank_exercise/${exerciseId.value}/${pathname.value}/${moduleId.value}`;
    } else {
      window.location.href = `/my/course/${courseId.value}`;
    }
  } else if (sign.value === 'optional') {
    signContractConfirmVisible.value = false;
    signContractVisible.value = false;
  }
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

const submitSignature = () => {
  if (!signature.value.isEmpty()) {
    formState.handSignature = signature.value.getPNG();
    signVisible.value = false;
    signContractVisible.value = true;
    message.success(t('message.submitSuccessfully'));
  } else {
    message.error(t('message.enterHandwrittenSignature'));
  }
};

const clearSignature = () => {
  signature.value.clear();
};

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
  try {
    await Api.contract.sign(contractId.value, params);
    message.success(t('message.signedSuccessfully'));
  } catch (e) {

  } finally {
    signContractVisible.value = false;
  }
};

const submitIsDisabled = () => {
  const {IDNumber, phoneNumber, handSignature} = contract.value.sign;
  const fieldsToCheck = [
    {key: 'truename', value: formState.truename},
    {key: 'IDNumber', value: IDNumber},
    {key: 'phoneNumber', value: phoneNumber},
    {key: 'handSignature', value: handSignature}
  ];
  return !fieldsToCheck.every(({key, value}) =>
    value === 0 || formState[key] !== undefined && formState[key] !== ''
  );
};

const { locale } = useI18n();
const bgClass = computed(() => {
  return locale.value.startsWith('zh')
    ? "bg-[url('img/sign-contract/bg-01.svg')]"
    : "bg-[url('img/sign-contract/bg-02.svg')]";
});
</script>

<template>
  <div>
    <!--    签署合同确认框-->
    <a-modal v-model:open="signContractConfirmVisible" :centered="true" :maskClosable="false" :closable=false
             :cancelText="t('btn.cancel')" :okText="t('btn.goToSign')" width="416px"
             wrapClassName="to-sign-contract-modal" :onCancel="toCoursePage" :onOk="toSignContract">
      <div class="flex">
        <ExclamationCircleOutlined class="mr-16 w-22 h-22 text-[#FAAD14]" style="font-size: 22px"/>
        <div class="flex flex-col">
          <div class="text-16 text-[#1E2226] font-medium mb-8">{{ t('modal.SignAnElectronicContract') }}</div>
          <div class="text-14 text-[#626973] font-normal">
            {{ `${t('modal.confirmContentPart01')}《${contractTemplate.name}》${t('modal.confirmContentPart02')}` }}
          </div>
        </div>
      </div>
    </a-modal>

    <!--  签署合同页面-->
    <a-modal v-model:open="signContractVisible" :centered="true" :maskClosable="false" :closable=false width="900px"
             wrapClassName="sign-contract-modal">
      <template #title>
        <div class="flex justify-between items-center px-24 py-16 border-b border-[#DCDEE0]">
          <div class="text-16 text-[#1E2226] font-medium">{{ t('modal.signContract') }}</div>
          <CloseOutlined class="h-16 w-16" @click="toCoursePage"/>
        </div>
      </template>
      <div class="p-24 flex">
        <div class="flex flex-1 mr-32 border border-solid border-[#DFE2E6] rounded-8 relative h-380">
          <div class="flex flex-col overflow-y-auto overscroll-auto pt-20 w-full rounded-8 px-32"
               style="height: calc(100% - 53px);">
            <div v-html="contractTemplate.content" class="text-12 text-[#626973] font-normal leading-20 mb-32"></div>
          </div>
          <div
            class="absolute bottom-0 z-10 flex justify-center items-center rounded-b-8 w-full py-16 border-t border-x-0 border-b-0 border-[#DFE2E6] border-solid hover:cursor-pointer bg-white"
            @click="showContractDetailModal">
            <img src="../../img/sign-contract/icon-01.jpg" class="w-16 h-16 mr-10" alt="">
            <div class="text-[#3DCD7F] text-14 font-normal">{{ t('modal.viewContractDetails') }}</div>
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
              :label="t('label.partyBName')"
              :validateTrigger="['blur']"
              :label-col="{ span: 8 }"
              :wrapper-col="{ span: 10 }"
              name="truename"
              :rules="[
                { required: true, message: t('validation.enterName') },
                { pattern: /^[\u4e00-\u9fa5a-zA-Z]+$/, message: t('validation.enterNumberOrChineseCharacters') }
              ]"
            >
              <a-input v-model:value="formState.truename" :placeholder="t('placeholder.pleaseEnter')"/>
            </a-form-item>
            <a-form-item
              :label="t('label.partyBiDNumber')"
              :validateTrigger="['blur']"
              :label-col="{ span: 8 }"
              :wrapper-col="{ span: 16 }"
              name="IDNumber"
              v-if="contract.sign.IDNumber === 1"
              :rules="[
                { required: true, message: t('validation.enterIDNumber') },
                { pattern: /^[1-9]\d{5}(19|20)\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[\dXx]$/, message: t('validation.IDNumberFormat') }
              ]"
            >
              <a-input v-model:value="formState.IDNumber" class="w-full" :placeholder="t('placeholder.pleaseEnter')"/>
            </a-form-item>
            <a-form-item
              :label="t('label.contactInformationOfPartyB')"
              :validateTrigger="['blur']"
              :label-col="{ span: 8 }"
              :wrapper-col="{ span: 12 }"
              name="phoneNumber"
              v-if="contract.sign.phoneNumber === 1"
              :rules="[
                { required: true, message: t('validation.enterContactInformation') },
                { pattern:  /^1\d{10}$/, message: t('validation.enterNumber') }
              ]"
            >
              <a-input v-model:value="formState.phoneNumber" :maxlength="11"
                       :placeholder="t('placeholder.pleaseEnter')"/>
            </a-form-item>
            <a-form-item
              :label="t('label.handwrittenSignature')"
              :label-col="{ span: 8 }"
              :wrapper-col="{ span: 12 }"
              name="handSignature"
              v-if="contract.sign.handSignature === 1"
              :rules="[
                { required: true, message: t('validation.enterHandwrittenSignature') },
              ]"
            >
              <div
                class="border-[#ebebeb] border-dashed border h-200 rounded-4 flex justify-center items-center mb-6 hover:cursor-pointer"
                @click="showSignModal">
                <div class="flex items-center" v-if="!formState.handSignature">
                  <EditOutlined class="h-16 mr-8"/>
                  <div class="text-[#1E2226] text-14 font-normal">{{ t('label.startSigning') }}</div>
                </div>
                <img v-if="formState.handSignature" class="w-160" :src="formState.handSignature" alt="">
                <div
                  class="absolute bg-black/40 rounded-4 justify-center items-center h-200 w-full flex opacity-0 hover:opacity-100"
                  v-if="formState.handSignature">
                  <div class="bg-white px-15 py-6 flex border h-fit border-solid border-[#DFE2E6] rounded-6">
                    <EditOutlined class="h-16 mr-8"/>
                    <div class="text-[#1E2226] text-14 font-normal">{{ t('label.reSign') }}</div>
                  </div>
                </div>
              </div>

              <div class="text-12 text-[#8A9099] font-normal">{{ t('tip.clickOnThisArea') }}</div>
            </a-form-item>

          </a-form>
        </div>
      </div>
      <template #footer>
        <div class="flex justify-center px-24 py-20">
          <a-button class="mr-8" @click="toCoursePage">{{ t('btn.close') }}</a-button>
          <a-button type="primary" @click="submitContract"
                    :disabled="submitIsDisabled()">
            {{ t('btn.confirmationSignature') }}
          </a-button>
        </div>
      </template>
    </a-modal>

    <!--  合同详情页面-->
    <a-modal v-model:open="contractDetailVisible" :maskClosable="false" width="100vw"
             wrapClassName="contract-detail-modal" :closable=false>
      <template #title>
        <div class="px-20 py-16 flex items-center">
          <div class="hover:cursor-pointer flex items-center" @click="contractDetailVisible = false;">
            <LeftOutlined class="h-14 mr-8"/>
            <div class="text-14 text-[#1E2226] font-normal">{{ t('btn.return') }}</div>
          </div>
          <a-divider type="vertical" class="mx-16"/>
          <div class="text-14 text-[#1E2226] font-normal mr-16">{{ t('label.contractParticular') }}</div>
        </div>
      </template>
      <div v-html="contractTemplate.content" class="text-12 mt-24 text-[#626973] font-normal w-900 leading-20"></div>
      <template #footer></template>
    </a-modal>

    <!--合同签字-->
    <a-modal v-model:open="signVisible" :centered="true" :maskClosable="false" :closable=false
             :cancelText="t('btn.close')" :okText="t('btn.confirmationSignature')" width="572px"
             wrapClassName="sign-contract-modal">
      <template #title>
        <div class="flex justify-between items-center px-24 py-16 border-b border-[#DCDEE0]">
          <div class="text-16 text-[#1E2226] font-medium">
            {{ `${targetTitle}-${nickname}-${t('modal.title.electronicContractSigning')}` }}
          </div>
          <CloseOutlined class="h-16 w-16" @click="closeSignModal"/>
        </div>
      </template>
      <div class="p-24 flex flex-col">
        <div class="text-center text-14 text-[#37393D] font-normal mb-32">{{ t('tip.makeSure') }}</div>
        <div class="relative flex items-center justify-center border-[#86909C] border bg-center bg-no-repeat bg-[url('img/sign-contract/bg-01.svg')] border-dashed rounded-8 h-256 w-full mb-8" :class="bgClass">
          <canvas id="canvas" class="rounded-8"></canvas>
        </div>
      </div>
      <template #footer>
        <div class="py-20 flex justify-center">
          <a-button class="mr-8" @click="clearSignature">{{ t('btn.clear') }}</a-button>
          <a-button type="primary" @click="submitSignature">{{ t('btn.submit') }}</a-button>
        </div>
      </template>
    </a-modal>
  </div>
</template>

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

.contract-detail-modal {
  .ant-modal {
    max-width: 100%;
    top: 0;
    padding-bottom: 0;
    margin: 0;

    .ant-modal-content {
      display: flex;
      flex-direction: column;
      height: 100%;
      padding: 0;
      border-radius: 0;
      top: 0;
    }

    .ant-modal-body {
      padding-top: 0;
      bottom: 0;
      padding-bottom: 120px;
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

    .ant-modal-header {
      padding: 0;
      margin-bottom: 0;
    }
  }
}

.ant-message {
  left: 50%;
  transform: translateX(-50%)
}
</style>
