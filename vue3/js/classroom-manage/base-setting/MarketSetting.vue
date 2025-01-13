<script setup>
import {computed, reactive, ref, h} from 'vue';
import Api from '../../../api';
import {DownOutlined, QuestionCircleOutlined, CloseOutlined} from '@ant-design/icons-vue';
import {t} from '../../course-manage/base-setting/vue-lang';
import dayjs from 'dayjs';

const props = defineProps({
  manage: {type: Object, default: {}}
});

const formRef = ref(null);
const formState = reactive({
  price: props.manage.classroom.price,
  buyable: props.manage.classroom.buyable,
  contractEnable: props.manage.classroom.contractId !== 0 ? 1 : 0,
  contractForceSign: props.manage.classroom.contractForceSign,
  contractId: props.manage.classroom.contractId,
  expiryMode: props.manage.classroom.expiryMode,
  expiryValue: props.manage.classroom.expiryMode === 'date' ? dayjs(Number(props.manage.classroom.expiryValue) * 1000) : props.manage.classroom.expiryValue,
  service: props.manage.classroom.service,
});
if (props.manage.vipInstalled && props.manage.vipEnabled === 1) {
  Object.assign(formState, {vipLevelId: props.manage.classroom.vipLevelId});
}

const statusRadios = [
  {label: '开启', value: '1'},
  {label: '关闭', value: '0'},
];

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
const contractName = ref(props.manage.classroom.contractName);
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
  contractPreview.value = await Api.contract.previewContract(id, props.manage.classroom.id);
  contractPreviewModalVisible.value = true;
};

const expiryModeRadios = [
  {label: '截止日期', value: 'date'},
  {label: '有效期天数', value: 'days'},
  {label: '长期有效', value: 'forever'},
];
const disabledPastDate = (current) => {
  return current && current < new Date().setHours(0, 0, 0, 0);
};

const serviceItem = ref(props.manage.serviceTags.map(item => ({
  ...item,
  active: item.active === 1 || formState.service.indexOf(item.code) >= 0,
})));
const selectServiceItem = (tag, index, checked) => {
  if (checked === true) {
    serviceItem.value[index].active = true;
    formState.service.push(tag.code);
  } else {
    serviceItem.value[index].active = false;
    formState.service.splice(formState.service.indexOf(tag.code), 1);
  }
};
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
    <div class="absolute w-full px-32 font-medium py-10 text-14 text-stone-900 bg-[#f5f5f5]">营销设置</div>
    <a-form
      ref="formRef"
      class="mt-66"
      :model="formState"
      :label-col="{ span: 4 }"
      :wrapper-col="{ span: 16 }"
    >
      <a-form-item
        label="价格"
        name="price"
        :validateTrigger="['blur']"
        :rules="[
          { required: true, message: '请输入价格' },
          { pattern: /^(\d{1,8}(\.\d{1,2})?)?$/, message: '请输入大于0的有效价格，最多两位小数，整数位不超过8位！' },
        ]"
      >
        <a-input v-model:value="formState.price"
                 suffix="元" style="width: 150px"></a-input>
        <div class="mt-5 text-[#a1a1a1] text-14 font-normal">当前共有 {{ props.manage.courseNum }} 个课程，原价共计
          {{ props.manage.coursePrice }} 元。
        </div>
        <div v-if="props.manage.coinSetting.coin_enabled && props.manage.coinSetting.price_type === 'Coin'"
             class="mt-5 text-[#a1a1a1] text-14 font-normal">相当于
          {{ formState.price * props.manage.coinSetting.cash_rate }} {{ props.manage.coinSetting.coin_name }}
        </div>
      </a-form-item>
      <a-form-item
        label="班级购买"
      >
        <div class="flex h-32 items-center">
          <a-radio-group v-model:value="formState.buyable">
            <a-radio
              v-for="item in statusRadios"
              :key="item.value"
              :value="item.value"
            >
              {{ item.label }}
            </a-radio>
          </a-radio-group>
        </div>
        <div class="mt-5 text-[#a1a1a1] text-14 font-normal">关闭后班级将无法在线购买加入。</div>
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
        label="班级有效期"
      >
        <div class="flex h-32 items-center mb-5">
          <a-radio-group v-model:value="formState.expiryMode" @change="formState.expiryValue = null" :disabled="props.manage.classroom.status !== 'draft'">
            <a-radio
              v-for="item in expiryModeRadios"
              :key="item.value"
              :value="item.value"
            >
              {{ item.label }}
            </a-radio>
          </a-radio-group>
          <a class="text-14 font-medium text-[--primary-color]" :href="props.manage.classroomExpiryRuleUrl"
             target="_blank">查看有效期规则</a>
        </div>
        <div v-if="formState.expiryMode === 'date'" class="flex">
          <a-form-item
            name="expiryValue"
            :validateTrigger="['blur']"
            :rules="[
            { required: true, message: '请输入截至日期' },
          ]"
          >
            <a-date-picker v-model:value="formState.expiryValue"
                           style="width: 150px" :disabled-date="disabledPastDate"/>
          </a-form-item>
          <div class="h-32 ml-5 text-14 text-[#a1a1a1] leading-32">在此日期前，学员可进行学习。</div>
        </div>
        <div v-if="formState.expiryMode === 'days'" class="flex">
          <a-form-item
            name="expiryValue"
            :validateTrigger="['blur']"
            :rules="[
            { required: true, message: '请输入有效期天数' },
            { pattern: /^([1-9]|[1-9]\d{1,2}|[1-6]\d{3}|7[0-2]\d{2}|7300)$/,message: '请输入不大于 7300（20年）的正整数' },
          ]"
          >
            <a-input v-model:value="formState.expiryValue"
                     suffix="天" style="width: 150px"></a-input>
          </a-form-item>
          <div class="h-32 ml-5 text-14 text-[#a1a1a1] leading-32">从加入当天起，在几天内可进行学习。</div>
        </div>
        <div class="text-[#ffa51f] text-14 font-normal">班级首次发布后，有效期类型不能再更改，只允许修改有效日期。</div>
      </a-form-item>
      <a-form-item
        v-if="props.manage.vipInstalled && props.manage.vipEnabled === 1"
        label="会员免费兑换"
      >
        <a-select
          v-model:value="formState.vipLevelId"
          style="width: 200px"
        >
          <a-select-option value="0">无</a-select-option>
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

  img {
    max-width: 100%;
  }
}
</style>
