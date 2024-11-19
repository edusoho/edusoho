<script setup>
import {computed, onMounted, reactive, ref} from 'vue';
import {
  PlusOutlined,
  LoadingOutlined,
  DownOutlined,
  CloseOutlined,
  QuestionCircleOutlined,
} from '@ant-design/icons-vue';
import {message} from 'ant-design-vue';
import Api from '../../../api';
import { t } from './vue-lang';
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
  buyExpiryTime: props.manage.course.buyExpiryTime == '0' ? null : props.manage.course.buyExpiryTime,
  taskDisplay: props.manage.course.taskDisplay,
  drainageEnabled: props.manage.course.drainageEnabled,
  deadline: props.manage.course.expiryEndDate == 0 ? null : props.manage.course.expiryEndDate,
  deadlineType: props.manage.course.deadlineType ? props.manage.course.deadlineType : 'days',
  expiryDays: props.manage.course.expiryDays > 0 ? props.manage.course.expiryDays : null,
  expiryMode: props.manage.course.expiryMode,
  expiryStartDate: props.manage.course.expiryStartDate == 0 ? null : props.manage.course.expiryStartDate,
  expiryEndDate: props.manage.course.expiryEndDate == 0 ? null : props.manage.course.expiryEndDate,
  contractEnable: props.manage.course.contractId !== 0 ? 1 : 0,
  contractForceSign: props.manage.course.contractForceSign,
  contractId: props.manage.course.contractId,
  services: props.manage.course.services ? props.manage.course.services : [],
  drainageImage: props.manage.course.drainageImage,
  drainageText: props.manage.course.drainageText,
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
}

const disabledEndDate = (current) => {
  if (formState.expiryStartDate !== null) {
    return current < formState.expiryStartDate;
  } else {
    return current <= Date.now() - 24 * 60 * 60 * 1000;
  }
}

const serviceItem = ref(props.manage.serviceTags.map(item => ({
  ...item,
  active: item.active === 1
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
    message.error('请上传jpg，gif，png格式的图');
    drainageLoading.value = false;
  }
  if (isJpgOrGifOrPng) {
    const formData = new FormData();
    formData.append('file', info.file.originFileObj);
    formData.append('group', 'system');
    fileData.value = await Api.file.upload(formData);
    drainageLoading.value = false;
    formState.drainageImage = fileData.value.uri;
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
  {label: '随到随学', value: 'days'},
  {label: '固定周期', value: 'date'},
  {label: '长期有效', value: 'forever'},
];

const liveCapacity = ref();
async function getLiveCapacity(url) {
  const res =  await Api.liveCapacity.get(url);
  liveCapacity.value = res.capacity;
}

onMounted(async () => {
  await getLiveCapacity(props.manage.liveCapacityUrl)
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
         style="width: calc(100% + 64px);">营销设置
    </div>
    <a-form
      ref="formRef"
      class="mt-66"
      :model="formState"
      :label-col="{ span: 4 }"
      :wrapper-col="{ span: 16 }"
    >
      <div v-if="props.manage.course.platform === 'supplier'">
        <a-form-item
          label="合作面额"
        >

        </a-form-item>

        <a-form-item
          label="建议售价"
        >

        </a-form-item>
      </div>

      <a-form-item
        label="价格"
        name="originPrice"
        :validateTrigger="['blur']"
        :rules="[
          { required: true, message: '请输入价格' },
          { pattern: /^(?!0\.?$)([1-9]\d{0,7}|0)(\.\d{1,2})?$/, message: '请输入大于0的有效价格，最多两位小数，整数位不超过8位！' },
        ]"
      >
        <a-input v-model:value="formState.originPrice"
                 :disabled="props.manage.course.platform === 'supplier' && !props.manage.canModifyCoursePrice"
                 suffix="元" style="width: 150px"></a-input>
      </a-form-item>

      <a-form-item
        name="buyable"
      >
        <template #label>
          <div class="flex items-center">
            <div>可加入</div>
            <a-popover>
              <template #content>
                <div class="text-14">
                  关闭后，前台显示为“限制课程”，学员自己无法加入，需要由老师手动添加学员。常用于封闭型教学。
                </div>
              </template>
              <QuestionCircleOutlined class="text-14 leading-14 mx-4"/>
            </a-popover>
          </div>
        </template>
        <a-radio-group v-model:value="formState.buyable">
          <a-radio value="1">可加入</a-radio>
          <a-radio value="0">不可加入</a-radio>
        </a-radio-group>
      </a-form-item>

      <a-form-item
        v-if="props.manage.courseSet.type === 'live'"
        label="限制加入人数"
        :validateTrigger="['blur']"
        :rules="[
          { required: true, message: '请输入限制加入人数' },
          { pattern: /^[0-9]\d*$/, message: '请输入非负整数' },
          ]"
      >
        <a-input v-model:value="formState.maxStudentNumL" class="mb-8" style="width: 150px;"></a-input>
        <div class="text-[#a1a1a1] text-12">加入直播课程人数限制，0为不限制人数</div>
        <div v-if="liveCapacity !== null && parseInt(formState.maxStudentNumL) > parseInt(liveCapacity)" class="text-[#F56C6C] text-12">
          网校可支持最多{{ liveCapacity }}人同时参加直播，您可以设置一个更大的数值，但届时有可能会导致满额后其他学员无法进入直播。
        </div>
      </a-form-item>

      <a-form-item>
        <template #label>
          <div class="flex items-center">
            <div>电子合同</div>
            <a-popover>
              <template #content>
                <div class="text-14">启用后，学员开始学习前需完成电子合同签署</div>
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
                    <div class="text-[#46c37b]" @click="previewContract(contract.id)">预览</div>
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
              <div>强制签署</div>
              <a-popover>
                <template #content>
                  <div class="text-14 w-350">
                    开启强制签署后，学员完成合同签署后才能学习，未签署的学员每次进入学习页面时弹窗提示签署合同。
                  </div>
                </template>
                <QuestionCircleOutlined class="text-14 leading-14 mx-4"/>
              </a-popover>
            </div>
          </template>
          <a-switch v-model:checked="contractForceSignSwitch"/>
        </a-form-item>
      </div>

      <a-form-item
        label="加入截止日期"
        :rules="[
          { required: true, message: '' },
          ]"
      >
        <a-radio-group v-model:value="formState.enableBuyExpiryTime">
          <a-radio value="0">不限时间</a-radio>
          <a-radio value="1">自定义</a-radio>
        </a-radio-group>
        <a-date-picker v-if="formState.enableBuyExpiryTime === '1'" :disabled-date="disabledPastDate"
                       v-model:value="formState.buyExpiryTime" style="width: 150px"/>
      </a-form-item>

      <a-form-item>
        <template #label>
          <div class="flex items-center">
            <div>学习有效期</div>
            <a-popover>
              <template #content>
                <div class="flex flex-col text-14">
                  <div class="mb-10"><span class="font-medium">随到随学：</span>有效期从学员加入的当天开始算起，截至到期当天晚上的23:59
                  </div>
                  <div class="mb-10"><span class="font-medium">固定周期：</span>有固定的学习开始日期和结束日期</div>
                  <div>过期后无法继续学习，系统会在到期前10天提醒学员。</div>
                </div>
              </template>
              <QuestionCircleOutlined class="text-14 leading-14 mx-4"/>
            </a-popover>
          </div>
        </template>
        <div class="flex flex-col">
          <div class="h-32 flex items-center">
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
          </div>
          <div class="mt-16 max-w-600 bg-[#f5f5f5] px-24 pt-24 flex flex-col" v-if="formState.expiryMode !== 'forever'">
            <a-radio-group
              v-if="formState.expiryMode === 'days'"
              :disabled="props.manage.course.status !== 'draft' || props.manage.course.platform !=='self'"
              v-model:value="formState.deadlineType"
            >
              <a-radio value="end_date">按截止日期</a-radio>
              <a-radio value="days">按有效天数</a-radio>
            </a-radio-group>
            <div v-if="formState.expiryMode === 'days' && formState.deadlineType === 'end_date'">
              <a-form-item
                name="deadline"
                :rules="[
                  { required: true, message: '请输入截至日期', trigger: blur },
                ]"
              >
                <div class="flex items-center mt-16">
                  <a-date-picker v-model:value="formState.deadline" :disabled="props.manage.course.platform !=='self'" style="width: 150px" :default-value="dayjs()" :disabled-date="disabledPastDate"/>
                  <div class="text-14 opacity-65 ml-10">在此日期前，学员可进行学习。</div>
                </div>
              </a-form-item>
            </div>
            <div class="flex" v-if="formState.expiryMode === 'days' && formState.deadlineType === 'days'">
              <a-form-item
                name="expiryDays"
                :validateTrigger="['blur']"
                :rules="[
                  { required: true, message: '请输入有效期天数' },
                  { pattern: /^([1-9]|[1-9]\d{1,2}|[1-6]\d{3}|7[0-2]\d{2}|7300)$/,message: '请输入不大于 7300（20年）的正整数' },
                ]"
              >
                <div class="flex items-center mt-16">
                  <a-input v-model:value="formState.expiryDays" :disabled="expiryValueDisabled || props.manage.course.platform !=='self'" style="width: 150px"/>
                  <div class="text-14 opacity-65 ml-10">从加入当天起，在几天内可进行学习。</div>
                </div>
              </a-form-item>
            </div>
            <div v-if="formState.expiryMode === 'date'" class="flex">
              <div class="text-14 mt-6 mr-4">开始日期</div>
              <a-form-item
                name="expiryStartDate"
                :validateTrigger="['blur']"
                :rules="[
                  { required: true, message: '请输入开始日期' },
                ]"
              >
                <a-date-picker v-model:value="formState.expiryStartDate" :disabled="expiryValueDisabled || props.manage.course.platform !=='self'" :disabled-date="disabledStartDate" style="width: 150px"/>
              </a-form-item>
              <div class="text-14 mt-6 mr-4 ml-8">结束日期</div>
              <a-form-item
                name="expiryEndDate"
                :validateTrigger="['blur']"
                :rules="[
                  { required: true, message: '请输入结束日期' },
                ]"
              >
                <a-date-picker v-model:value="formState.expiryEndDate" :disabled="expiryValueDisabled || props.manage.course.platform !=='self'" :disabled-date="disabledEndDate" style="width: 150px"/>
              </a-form-item>
            </div>
          </div>
          <div class="text-[#adadad] text-12 mt-8">
            教学计划一旦发布，有效期类型不能修改；课程或教学计划下架后，可以修改日期，新的学习有效期仅对修改后加入的学员生效
          </div>
        </div>
      </a-form-item>

      <a-form-item
        v-if="props.manage.vipInstalled && props.manage.vipEnabled"
        label="会员免费兑换"
        name="vipLevelId"
      >
        <a-select
          v-model:value="formState.vipLevelId"
          style="width: 200px"
        >
          <a-select-option value="0">无</a-select-option>
          <a-select-option
            v-if="props.manage.vipLevels"
            v-for="level in props.manage.vipLevels"
            :key="level.id"
            :value="level.id"
          >
            {{ level.name }}
          </a-select-option>
        </a-select>
      </a-form-item>

      <a-form-item
        label="承诺提供服务"
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
        label="商品页目录展示"
        name="taskDisplay"
      >
        <a-radio-group v-model:value="formState.taskDisplay">
          <a-radio value="1">开启</a-radio>
          <a-radio value="0">关闭</a-radio>
        </a-radio-group>
      </a-form-item>

      <a-form-item>
        <template #label>
          <div class="flex items-center">
            <div>引流设置</div>
            <a-popover>
              <template #content>
                <div class="text-14">
                  将已购用户引流至私域流量池
                </div>
              </template>
              <QuestionCircleOutlined class="text-14 leading-14 mx-4"/>
            </a-popover>
          </div>
        </template>
        <a-radio-group v-model:value="formState.drainageEnabled">
          <a-radio :value=1>开启</a-radio>
          <a-radio :value=0>关闭</a-radio>
        </a-radio-group>
      </a-form-item>

      <div v-if="formState.drainageEnabled === 1">
        <a-form-item
          label="二维码设置"
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
            <img v-if="formState.drainageImage" :src="formState.drainageImage" alt="" style="width: 100%" class="rounded-4"/>
            <div v-else>
              <loading-outlined v-if="drainageLoading"></loading-outlined>
              <plus-outlined v-else></plus-outlined>
              <div class="ant-upload-text">上传</div>
            </div>
          </a-upload>
          <div class="text-[#606266] text-12 ">请上传jpg，gif，png格式的图</div>
        </a-form-item>

        <a-form-item
          label="引流文案"
        >
          <a-input v-model:value="formState.drainageText" placeholder="请输入内容" show-count :maxlength="20"/>
        </a-form-item>

        <a-form-item
          label="引流页样式"
        >
          <div class="flex">
            <div class="opacity-65 text-14">加入/支付完成页</div>
            <a-popover>
              <template #content>
                <img src="../../../img/course-manage/base-setting/drainage-style.png" alt="drainage"
                     style="height: 500px">
              </template>
              <div class="text-[#409EFF] font-medium text-14 ml-4">查看详情</div>
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
        <div class="flex justify-between items-center px-24 py-16 border-solid border border-[#F0F0F0] border-t-0 border-x-0">
          <div class="text-16 text-[#1E2226] font-medium">
            {{ `${contractPreview.goodsName}-${t('modal.contractSigning')}` }}
          </div>
          <CloseOutlined class="h-16 w-16" @click="contractPreviewModalVisible = false"/>
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
              <div v-if="contractPreview.sign && contractPreview.sign.handSignature" class="flex items-center">
                <span class="text-gray-500">{{ `${t('modal.handSignature')}：` }}</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                  <img :src="contractPreview.sign.handSignature" class="h-35" alt=""/>
                </div>
              </div>
              <div v-if="contractPreview.sign && contractPreview.sign.truename" class="flex items-center">
                <span class="text-gray-500">{{ `${t('modal.partyBName')}：` }}</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                  {{ contractPreview.sign.truename }}
                </div>
              </div>
              <div v-if="contractPreview.sign && contractPreview.sign.IDNumber" class="flex items-center">
                <span class="text-gray-500">{{ `${t('modal.iDNumber')}：` }}</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                  {{ contractPreview.sign.IDNumber }}
                </div>
              </div>
              <div v-if="contractPreview.sign && contractPreview.sign.phoneNumber" class="flex items-center">
                <span class="text-gray-500">{{ `${t('modal.contactInformation')}：` }}</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                  {{ contractPreview.sign.phoneNumber }}
                </div>
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
}
</style>
