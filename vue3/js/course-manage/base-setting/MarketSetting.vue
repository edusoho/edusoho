<script setup>
import {computed, h, onMounted, reactive, ref} from 'vue';
import {
  PlusOutlined,
  LoadingOutlined,
  DownOutlined,
  CloseOutlined,
  QuestionCircleOutlined,
} from '@ant-design/icons-vue';
import {message} from 'ant-design-vue';
import Api from '../../../api';
import {t} from './vue-lang';
import dayjs from 'dayjs';

const props = defineProps({
  manage: {type: Object, default: {}}
});

const formRef = ref(null);
const formState = reactive({
  originPrice: props.manage.course.originPrice,
  buyable: props.manage.course.buyable,
  maxStudentNumL: props.manage.course.maxStudentNum,
  enableBuyExpiryTime: props.manage.course.buyExpiryTime > 0 ? '1' : '0',
  buyExpiryTime: props.manage.course.buyExpiryTime == '0' ? null : dayjs(Number(props.manage.course.buyExpiryTime) * 1000),
  taskDisplay: props.manage.course.taskDisplay,
  drainageEnabled: props.manage.course.drainageEnabled,
  deadline: props.manage.course.expiryEndDate == 0 ? null : dayjs(props.manage.course.expiryEndDate),
  deadlineType: props.manage.course.deadlineType ? props.manage.course.deadlineType : 'days',
  expiryDays: props.manage.course.expiryDays > 0 ? props.manage.course.expiryDays : null,
  expiryMode: props.manage.course.expiryMode,
  expiryStartDate: props.manage.course.expiryStartDate == 0 ? null : dayjs(props.manage.course.expiryStartDate),
  expiryEndDate: props.manage.course.expiryEndDate == 0 ? null : dayjs(props.manage.course.expiryEndDate),
  contractEnable: props.manage.course.contractId !== 0 ? 1 : 0,
  contractForceSign: props.manage.course.contractForceSign,
  contractId: props.manage.course.contractId,
  services: props.manage.course.services,
  drainageImage: props.manage.course.drainageImage,
  drainageText: props.manage.course.drainageText,
  hidePrice: props.manage.course.hidePrice,
});
if (props.manage.vipInstalled && props.manage.vipEnabled) {
  Object.assign(formState, {vipLevelId: props.manage.course.vipLevelId});
}

const coursePublished = ref(props.manage.course.status ? props.manage.course.status === 'published' : false);
const courseSetPublished = ref(props.manage.courseSet.status ? props.manage.courseSet.status === 'published' : false);
const courseClosed = ref(props.manage.course.status ? props.manage.course.status === 'closed' : false);
const courseSetClosed = ref(props.manage.courseSet.status ? props.manage.courseSet.status === 'closed' : false);
const expiryValueDisabled = ref((coursePublished.value && courseSetPublished.value) || courseClosed.value || courseSetClosed.value);

const disabledPastDate = (current) => {
  return current && current < new Date().setHours(0, 0, 0, 0);
};

const disabledStartDate = (current) => {
  if (formState.expiryEndDate !== '' && formState.expiryEndDate != null) {
    return current > formState.expiryEndDate || current <= Date.now() - 24 * 60 * 60 * 1000;
  } else {
    return current <= Date.now() - 24 * 60 * 60 * 1000;
  }
};

const disabledEndDate = (current) => {
  if (formState.expiryStartDate !== null) {
    return current < formState.expiryStartDate;
  } else {
    return current <= Date.now() - 24 * 60 * 60 * 1000;
  }
};

const serviceItem = ref(props.manage.serviceTags.map(item => ({
  ...item,
  active: item.active === 1 || formState.services.indexOf(item.code) >= 0
})));
const selectServiceItem = (tag, index, checked) => {
  if (checked === true) {
    serviceItem.value[index].active = true;
    formState.services.push(tag.code);
  } else {
    serviceItem.value[index].active = false;
    formState.services.splice(formState.services.indexOf(tag.code), 1);
  }
};

const drainageLoading = ref(false);
const fileData = ref();
const uploadDrainageImage = async (info) => {
  drainageLoading.value = true;
  const isJpgOrGifOrPng = info.file.type === 'image/jpeg' || info.file.type === 'image/gif' || info.file.type === 'image/png';
  if (!isJpgOrGifOrPng) {
    message.error(t('validate.imgTypeLimit'));
    drainageLoading.value = false;
  }
  if (isJpgOrGifOrPng) {
    const formData = new FormData();
    formData.append('file', info.file.originFileObj);
    formData.append('group', 'system');
    fileData.value = await Api.file.upload(formData);
    drainageLoading.value = false;
    formState.drainageImage = fileData.value.uri;
    formRef.value.validateFields(['drainageImage']);
  }
};

const contractEnableSwitch = computed({
  get() {
    return formState.contractEnable === 1;
  },
  set(value) {
    formState.contractEnable = value ? 1 : 0;
  },
});

const contractForceSignSwitch = computed({
  get() {
    return formState.contractForceSign === 1;
  },
  set(value) {
    formState.contractForceSign = value ? 1 : 0;
  },
});

const contracts = ref();
const contractName = ref(props.manage.course.contractName);
const contractMenuVisible = ref(false);
const contractPreview = ref();
const contractPreviewModalVisible = ref(false);
const fetchContracts = async () => {
  contracts.value = await Api.contract.getSimpleContracts();
  if (contracts.value.length === 0) {
    return;
  }
  if (formState.contractId === 0) {
    formState.contractId = contracts.value[0].id;
    contractName.value = contracts.value[0].name;
  }
};
fetchContracts();

const selectContract = (id, name) => {
  formState.contractId = id;
  contractName.value = name;
  contractMenuVisible.value = false;
};

const previewContract = async (id) => {
  contractPreview.value = await Api.contract.previewContract(id, props.manage.course.id);
  contractPreviewModalVisible.value = true;
};

const expiryModeOptions = [
  {label: t('label.learning'), value: 'days'},
  {label: t('label.fixedCycle'), value: 'date'},
  {label: t('label.longTerm'), value: 'forever'},
];

const liveCapacity = ref();

async function getLiveCapacity(url) {
  const res = await Api.liveCapacity.get(url);
  liveCapacity.value = res.capacity;
}

onMounted(async () => {
  await getLiveCapacity(props.manage.liveCapacityUrl);
});

const validateForm = () => {
  return formRef.value.validate()
    .then(() => {
      return formState;
    })
    .catch((error) => {
    });
};

defineExpose({
  validateForm,
});
</script>

<template>
  <div class="flex flex-col w-full relative">
    <div class="absolute -left-32 w-full px-32 font-medium py-10 text-14 text-stone-900 bg-[#f5f5f5]"
         style="width: calc(100% + 64px);">{{ t('title.marketingSettings') }}
    </div>
    <a-form
      ref="formRef"
      class="mt-66"
      :model="formState"
      :label-col="{ span: 6 }"
      :wrapper-col="{ span: 16 }"
    >
      <a-form-item
        :label="t('label.price')"
        name="originPrice"
        :validateTrigger="['blur']"
        :rules="[
          { required: true, message: t('validate.enterPrice') },
          { pattern: /^(\d{1,8}(\.\d{1,2})?)?$/, message: t('validate.validPrice') },
        ]"
      >
        <div class="flex items-center gap-24">
          <a-input v-model:value="formState.originPrice"
                   :disabled="props.manage.course.platform === 'supplier' && !props.manage.canModifyCoursePrice"
                   :suffix="t('label.rmb')" style="width: 150px"></a-input>
          <a-form-item-rest>
            <a-radio-group v-model:value="formState.hidePrice">
              <a-radio value="0">{{ t('label.displayPrice') }}</a-radio>
              <a-radio value="1">{{ t('label.notDisplayPrice') }}</a-radio>
            </a-radio-group>
          </a-form-item-rest>
        </div>
      </a-form-item>
      <a-form-item>
        <template #label>
          <div class="flex items-center">
            <div>{{ t('label.canBeAdded') }}</div>
            <a-popover>
              <template #content>
                <div class="text-14">{{ t('tip.canBeAdded') }}</div>
              </template>
              <QuestionCircleOutlined class="text-14 leading-14 mx-4"/>
            </a-popover>
          </div>
        </template>
        <a-radio-group v-model:value="formState.buyable">
          <a-radio value="1">{{ t('label.canBeAdded') }}</a-radio>
          <a-radio value="0">{{ t('label.doNotAdd') }}</a-radio>
        </a-radio-group>
      </a-form-item>
      <a-form-item
        v-if="props.manage.courseSet.type === 'live'"
        name="maxStudentNumL"
        :label="t('label.limitNumber')"
        :validateTrigger="['blur']"
        :rules="[
          { required: true, message: t('validate.enterLimit') },
          { pattern: /^[0-9]\d*$/, message: t('validate.integer') },
          ]"
      >
        <a-input v-model:value="formState.maxStudentNumL" class="mb-8" style="width: 150px;"></a-input>
        <div class="text-[#a1a1a1] text-14">{{ t('tip.numberOfParticipants') }}</div>
        <div v-if="liveCapacity !== null && parseInt(formState.maxStudentNumL) > parseInt(liveCapacity)"
             class="text-[#F56C6C] text-14">
          {{ t('tip.supportPeople', {liveCapacity: liveCapacity}) }}
        </div>
      </a-form-item>
      <a-form-item>
        <template #label>
          <div class="flex items-center">
            <div>{{ t('label.electronicContract') }}</div>
            <a-popover>
              <template #content>
                <div class="text-14">{{ t('tip.electronicContract') }}</div>
              </template>
              <QuestionCircleOutlined class="text-14 leading-14 mx-4"/>
            </a-popover>
          </div>
        </template>
        <div class="h-32 flex items-center mb-6">
          <a-switch v-model:checked="contractEnableSwitch"/>
        </div>
        <div v-if="contractEnableSwitch">
          <a-dropdown :trigger="['click']" placement="bottom" v-model:value="contractMenuVisible">
            <a-button>
              <div class="flex">
                <div class="w-150 truncate text-left">{{ contractName }}</div>
                <DownOutlined class="ml-16 text-[#d9d9d9] text-12"/>
              </div>
            </a-button>
            <template #overlay>
              <a-menu class="h-350 overflow-y-scroll">
                <a-menu-item
                  v-for="contract in contracts"
                  :key="contract.id"
                >
                  <div class="flex justify-between">
                    <div class="w-150 truncate text-left" @click="selectContract(contract.id, contract.name)">
                      {{ contract.name }}
                    </div>
                    <div class="text-[#46c37b]" @click="previewContract(contract.id)">{{ t('btn.preview') }}</div>
                  </div>
                </a-menu-item>
              </a-menu>
            </template>
          </a-dropdown>
        </div>
      </a-form-item>
      <div v-if="contractEnableSwitch">
        <a-form-item>
          <template #label>
            <div class="flex items-center">
              <div>{{ t('label.compulsorySigning') }}</div>
              <a-popover>
                <template #content>
                  <div class="text-14 w-350">{{ t('tip.compulsorySigning') }}</div>
                </template>
                <QuestionCircleOutlined class="text-14 leading-14 mx-4"/>
              </a-popover>
            </div>
          </template>
          <a-switch v-model:checked="contractForceSignSwitch"/>
        </a-form-item>
      </div>
      <a-form-item
        :label="t('label.joiningDeadline')"
        :required="true"
      >
        <div class="flex">
          <a-radio-group v-model:value="formState.enableBuyExpiryTime"
                         :class="{'mb-24': formState.enableBuyExpiryTime === '1'}" class="flex items-center">
            <a-radio value="0">{{ t('label.noTimeLimit') }}</a-radio>
            <a-radio value="1">{{ t('label.custom') }}</a-radio>
          </a-radio-group>
          <a-form-item
            v-if="formState.enableBuyExpiryTime === '1'"
            name="buyExpiryTime"
            :validateTrigger="['blur']"
            :rules="[{ required: true, message: t('validate.enterDeadline') }]"
          >
            <a-date-picker v-model:value="formState.buyExpiryTime" :disabled-date="disabledPastDate"
                           style="width: 150px"/>
          </a-form-item>
        </div>
      </a-form-item>
      <a-form-item>
        <template #label>
          <div class="flex items-center">
            <div>{{ t('label.validityPeriod') }}</div>
            <a-popover>
              <template #content>
                <div class="flex flex-col text-14">
                  <div class="mb-10"><span class="font-medium">{{ t('tip.dropLearning') }}：</span>{{ t('tip.validityPeriod') }}</div>
                  <div class="mb-10"><span class="font-medium">{{ t('tip.fixedCycle') }}：</span>{{ t('tip.fixedDate') }}</div>
                  <div>{{ t('tip.expirationDate') }}</div>
                </div>
              </template>
              <QuestionCircleOutlined class="text-14 leading-14 mx-4"/>
            </a-popover>
          </div>
        </template>
        <div class="flex flex-col">
          <div class="h-32 flex items-center">
            <a-form-item-rest>
              <a-radio-group v-model:value="formState.expiryMode">
                <a-radio
                  v-for="option in expiryModeOptions"
                  :key="option.value"
                  :value="option.value"
                  :disabled="props.manage.course.status !== 'draft' || props.manage.course.platform !=='self'"
                >
                  {{ option.label }}
                </a-radio>
              </a-radio-group>
            </a-form-item-rest>
          </div>
          <div class="mt-16 max-w-600 bg-[#f5f5f5] px-24 pt-24 flex flex-col"
               v-if="formState.expiryMode !== 'forever'">
            <a-form-item-rest>
              <a-radio-group
                v-if="formState.expiryMode === 'days'"
                :disabled="props.manage.course.status !== 'draft' || props.manage.course.platform !=='self'"
                v-model:value="formState.deadlineType"
              >
                <a-radio value="end_date">{{ t('label.deadline') }}</a-radio>
                <a-radio value="days">{{ t('label.validDays') }}</a-radio>
              </a-radio-group>
            </a-form-item-rest>
            <div v-if="formState.expiryMode === 'days' && formState.deadlineType === 'end_date'">
              <a-form-item
                name="deadline"
                :validateTrigger="['blur']"
                :rules="[
                  { required: true, message: t('validate.enterDeadline') },
                ]"
              >
                <div class="flex items-center mt-16">
                  <a-date-picker v-model:value="formState.deadline"
                                 :disabled="props.manage.course.platform !=='self'"
                                 style="width: 150px" :disabled-date="disabledPastDate"/>
                  <div class="text-14 opacity-65 ml-10">{{ t('tip.deadline') }}</div>
                </div>
              </a-form-item>
            </div>
            <div class="flex" v-if="formState.expiryMode === 'days' && formState.deadlineType === 'days'">
              <a-form-item
                name="expiryDays"
                :validateTrigger="['blur']"
                :rules="[
                  { required: true, message: t('validate.enterValidityPeriod') },
                  { pattern: /^([1-9]|[1-9]\d{1,2}|[1-6]\d{3}|7[0-2]\d{2}|7300)$/,message: t('validate.years') },
                ]"
              >
                <div class="flex items-center mt-16">
                  <a-input v-model:value="formState.expiryDays"
                           :disabled="expiryValueDisabled || props.manage.course.platform !=='self'"
                           style="width: 150px"/>
                  <div class="text-14 opacity-65 ml-10">{{ t('tip.expiryDays') }}</div>
                </div>
              </a-form-item>
            </div>
            <div v-if="formState.expiryMode === 'date'" class="flex">
              <div class="text-14 mt-6 mr-4">{{ t('label.startDate') }}</div>
              <a-form-item
                name="expiryStartDate"
                :validateTrigger="['blur']"
                :rules="[
                  { required: true, message: t('validate.enterStartDate') },
                ]"
              >
                <a-date-picker v-model:value="formState.expiryStartDate"
                               :disabled="expiryValueDisabled || props.manage.course.platform !=='self'"
                               :disabled-date="disabledStartDate" style="width: 150px"/>
              </a-form-item>
              <div class="text-14 mt-6 mr-4 ml-8">{{ t('label.endingDate') }}</div>
              <a-form-item
                name="expiryEndDate"
                :validateTrigger="['blur']"
                :rules="[
                  { required: true, message: t('validate.enterEndingDate') },
                ]"
              >
                <a-date-picker v-model:value="formState.expiryEndDate"
                               :disabled="expiryValueDisabled || props.manage.course.platform !=='self'"
                               :disabled-date="disabledEndDate" style="width: 150px"/>
              </a-form-item>
            </div>
          </div>
          <div class="text-[#adadad] text-14 mt-8">{{ t('tip.endDate') }}</div>
        </div>
      </a-form-item>
      <a-form-item
        v-if="props.manage.vipInstalled && props.manage.vipEnabled"
        :label="t('label.membersFree')"
      >
        <a-select
          v-model:value="formState.vipLevelId"
          style="width: 200px"
        >
          <a-select-option value="0">{{ t('label.nothing') }}</a-select-option>
          <a-select-option
            v-if="props.manage.vipLevels.length > 0"
            v-for="level in props.manage.vipLevels"
            :key="level.id"
            :value="level.id"
          >
            {{ level.name }}
          </a-select-option>
        </a-select>
      </a-form-item>
      <a-form-item
        :label="t('label.provideServices')"
      >
        <a-checkable-tag
          v-for="(tag, index) in serviceItem"
          :key="tag"
          v-model:checked="tag.active"
          @change="checked => selectServiceItem(tag, index, checked)"
        >
          <a-popover>
            <template #content>
              <div>{{ tag.summary }}</div>
            </template>
            <div class="text-14">{{ tag.fullName }}</div>
          </a-popover>
        </a-checkable-tag>
      </a-form-item>
      <a-form-item
        :label="t('label.catalogueDisplay')"
      >
        <a-radio-group v-model:value="formState.taskDisplay">
          <a-radio value="1">{{ t('label.open') }}</a-radio>
          <a-radio value="0">{{ t('label.close') }}</a-radio>
        </a-radio-group>
      </a-form-item>
      <a-form-item>
        <template #label>
          <div class="flex items-center">
            <div>{{ t('label.drainageSetting') }}</div>
            <a-popover>
              <template #content>
                <div class="text-14">{{ t('tip.drainageSetting') }}</div>
              </template>
              <QuestionCircleOutlined class="text-14 leading-14 mx-4"/>
            </a-popover>
          </div>
        </template>
        <a-radio-group v-model:value="formState.drainageEnabled">
          <a-radio :value=1>{{ t('label.open') }}</a-radio>
          <a-radio :value=0>{{ t('label.close') }}</a-radio>
        </a-radio-group>
      </a-form-item>
      <div v-if="formState.drainageEnabled === 1">
        <a-form-item
          :label="t('label.QRCodeSettings')"
          name="drainageImage"
          :validateTrigger="['blur']"
          :rules="[
            { required: true, message: t('validate.enterQRCode') },
          ]"
        >
          <a-upload
            ref="upload"
            class="drainage-uploader"
            accept="image/jpeg, image/gif, image/png"
            :file-list="[]"
            :maxCount="1"
            list-type="picture-card"
            @change="uploadDrainageImage"
          >
            <img v-if="formState.drainageImage" :src="formState.drainageImage" alt="" style="max-width: 100%; max-height: 100%;"
                 class="rounded-4"/>
            <div v-else>
              <loading-outlined v-if="drainageLoading"></loading-outlined>
              <plus-outlined v-else></plus-outlined>
              <div class="ant-upload-text">{{ t('btn.uploading') }}</div>
            </div>
          </a-upload>
          <div class="text-[#a1a1a1] text-14">{{ t('validate.imgTypeLimit') }}</div>
        </a-form-item>
        <a-form-item
          :label="t('label.attractingAttentionCopywriting')"
        >
          <a-input v-model:value="formState.drainageText" :placeholder="t('placeholder.provideContent')" show-count :maxlength="20"/>
        </a-form-item>
        <a-form-item
          :label="t('label.referralPage')"
        >
          <div class="flex">
            <div class="opacity-65 text-14">{{ t('tip.completionPage') }}</div>
            <a-popover>
              <template #content>
                <img src="../../../img/course-manage/base-setting/drainage-style.png" alt="drainage"
                     style="height: 500px">
              </template>
              <div class="text-[--primary-color] font-medium text-14 ml-4 cursor-pointer">{{ t('btn.viewDetails') }}</div>
            </a-popover>
          </div>
        </a-form-item>
      </div>
    </a-form>
    <a-modal :width="900"
             v-model:open="contractPreviewModalVisible"
             :closable=false
             :zIndex=1050
             :centered="true"
             :bodyStyle="{ 'height': '563px', 'overflow': 'auto'}"
             wrapClassName="market-setting-contract-detail-modal"
    >
      <template #title>
        <div
          class="flex justify-between items-center px-24 py-16 border-solid border border-[#F0F0F0] border-t-0 border-x-0">
          <div class="text-16 text-[#1E2226] font-medium">
            {{ `${contractPreview.goodsName}-${t('modal.contractSigning')}` }}
          </div>
          <a-button :icon="h(CloseOutlined)" type="text" size="small" @click="contractPreviewModalVisible = false"/>
        </div>
      </template>
      <div class="w-full flex flex-col space-y-32 p-32">
        <div class="flex items-end justify-between gap-4">
          <span class="flex-none whitespace-nowrap opacity-0">{{
              `${t('modal.contractNumber')}: ${contractPreview.code}`
            }}</span>
          <span class="grow text-center text-22 font-medium">{{ contractPreview.name }}</span>
          <span class="flex-none whitespace-nowrap text-gray-500">{{
              `${t('modal.contractNumber')}: ${contractPreview.code}`
            }}</span>
        </div>
        <div v-html="contractPreview.content" class="text-gray-500 contract-content"></div>
        <div class="flex space-x-64">
          <div class="flex-1 flex flex-col items-start justify-between space-y-22">
            <span class="text-18 font-medium">{{ `${t('modal.partyA')}：` }}</span>
            <div class="w-full flex flex-col space-y-22">
              <img :src="contractPreview.seal" alt="" class="w-150 h-150"/>
              <div class="flex items-center">
                <span class="text-gray-500">{{ `${t('modal.signingDate')}：` }}</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                  {{ contractPreview.signDate }}
                </div>
              </div>
            </div>
          </div>
          <div class="flex-1 flex flex-col items-start justify-between">
            <span class="text-18 font-medium">{{ `${t('modal.partyB')}：` }}</span>
            <div class="w-full flex flex-col space-y-22">
              <div v-if="contractPreview.sign?.handSignature" class="flex items-center">
                <span class="text-gray-500">{{ `${t('modal.handSignature')}：` }}</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium h-23"></div>
              </div>
              <div class="flex items-center">
                <span class="text-gray-500">{{ `${t('modal.partyBName')}：` }}</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium h-23"></div>
              </div>
              <div v-if="contractPreview.sign?.IDNumber" class="flex items-center">
                <span class="text-gray-500">{{ `${t('modal.iDNumber')}：` }}</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium h-23"></div>
              </div>
              <div v-if="contractPreview.sign?.phoneNumber" class="flex items-center">
                <span class="text-gray-500">{{ `${t('modal.contactInformation')}：` }}</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium h-23"></div>
              </div>
              <div class="flex items-center">
                <span class="text-gray-500">{{ `${t('modal.signingDate')}：` }}</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                  {{ contractPreview.signDate }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <template #footer>
        <div class="flex justify-center">
          <a-button @click="contractPreviewModalVisible = false">{{ t('btn.close') }}</a-button>
        </div>
      </template>
    </a-modal>
  </div>
</template>

<style lang="less">
.market-setting-contract-detail-modal {
  .ant-modal {
    padding: 0 !important;

    .ant-modal-content {
      padding: 0 !important;

      .ant-modal-footer {
        border-top: 1px solid #ebebeb;
        padding: 10px 16px;
        margin-top: 0;
      }

      .ant-modal-header {
        padding: 0;
        margin-bottom: 0;
        border: none;
      }
    }
  }

  img {
    max-width: 100%;
  }
}
</style>
