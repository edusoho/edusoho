<script setup>
import {computed, reactive, ref} from 'vue';
import {QuestionCircleOutlined} from '@ant-design/icons-vue';
import dayjs from 'dayjs';
import {
  PlusOutlined,
  LoadingOutlined,
  DownOutlined,
  CloseOutlined,
} from '@ant-design/icons-vue';
import {message} from 'ant-design-vue';
import Api from '../../../api';
import {t} from '../../my-contract/vue-lang';

const props = defineProps({
  manage: {type: Object, default: {}}
});


const formRef = ref(null);
const formState = reactive({
  originPrice: props.manage.course.originPrice,
  buyable: props.manage.course.buyable,
  maxStudentNumL: props.manage.course.maxStudentNum,
  enableBuyExpiryTime: props.manage.course.buyExpiryTime > 0 ? '1' : '0',
  buyExpiryTime: props.manage.course.buyExpiryTime === '0' ? null : props.manage.course.buyExpiryTime,
  taskDisplay: props.manage.course.taskDisplay,
  drainageEnabled: props.manage.course.drainageEnabled,
  contractEnable: props.manage.course.contractId !== 0 ? 1 : 0,
  contractForceSign: props.manage.course.contractForceSign,
  contractId: props.manage.course.contractId,
  services: props.manage.course.services ? props.manage.course.services : [],
  drainageImage: props.manage.course.drainageImage,
  drainageText: props.manage.course.drainageText,
});
if (props.manage.vipInstalled && props.manage.vipEnabled) {
  Object.assign(formState, { vipLevelId: props.manage.course.vipLevelId })
}

const positivePrice = (rule, value) => {
  return new Promise((resolve, reject) => {
    if (!/^[0-9]{0,8}(\.\d{0,2})?$/.test(value)) {
      reject(new Error(`请输入大于0的有效价格，最多两位小数，整数位不超过8位！`));
    }
    resolve();
  });
};

const disabledDate = (current) => {
  return current && current < dayjs().endOf('day');
};

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
    formState.services.splice(formState.services.indexOf(tag.code), 1)
  }
};

const drainageLoading = ref(false);
const fileData = ref();
const uploadDrainageImage = async (info) => {
  drainageLoading.value = true;
  const isJpgOrGifOrPng = info.file.type === 'image/jpg' || info.file.type === 'image/gif' || info.file.type === 'image/png';
  if (!isJpgOrGifOrPng) {
    message.error('请上传jpg，gif，png格式的图');
    drainageLoading.value = false;
  }
  if (isJpgOrGifOrPng) {
    const formData = new FormData();
    formData.append('file', info.file.originFileObj, info.file.name);
    formData.append('group', 'system');
    fileData.value = await Api.file.upload(formData);
    drainageLoading.value = false;
    formState.drainageImage = fileData.value.uri;
  }
}

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
}
fetchContracts();

const selectContract = (id, name) => {
  formState.contractId = id;
  contractName.value = name;
  contractMenuVisible.value = false;
}

const previewContract = async (id) => {
  contractPreview.value = await Api.contract.previewContract(id, props.manage.course.id);
  contractPreviewModalVisible.value = true;
};
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
      name="baseInfo"
      :label-col="{ span: 4 }"
      :wrapper-col="{ span: 16 }"
      autocomplete="off"
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
          { validator: positivePrice },
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
        <a-radio-group v-model:value="formState.buyable" class="market-setting-radio">
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
        <div class="text-[#a1a1a1] text-14">加入直播课程人数限制，0为不限制人数</div>
        <div>

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
                <div class="w-150 truncate text-left">{{contractName}}</div>
                <DownOutlined class="ml-20 text-[#d9d9d9] text-12"/>
              </div>
            </a-button>
            <template #overlay>
              <a-menu>
                <a-menu-item
                  v-for="contract in contracts"
                  :key="contract.id"
                >
                  <div class="flex justify-between">
                    <div class="w-150 truncate text-left" @click="selectContract(contract.id, contract.name)">{{contract.name}}</div>
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
                  <div class="text-14 w-350">开启强制签署后，学员完成合同签署后才能学习，未签署的学员每次进入学习页面时弹窗提示签署合同。</div>
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
        name="enableBuyExpiryTime"
        :rules="[
          { required: true, message: '' },
          ]"
      >
        <a-radio-group v-model:value="formState.enableBuyExpiryTime" class="market-setting-radio">
          <a-radio value="0">不限时间</a-radio>
          <a-radio value="1">自定义</a-radio>
        </a-radio-group>
        <a-date-picker v-if="formState.enableBuyExpiryTime === '1'" :disabled-date="disabledDate" v-model:value="formState.buyExpiryTime"
                       style="width: 150px"/>
      </a-form-item>

      <a-form-item
        label="学习有效期"
      >

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
            {{level.name}}
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
              <div>{{tag.summary}}</div>
            </template>
            <div class="text-14">{{ tag.fullName }}</div>
          </a-popover>
        </a-checkable-tag>
      </a-form-item>

      <a-form-item
        label="商品页目录展示"
        name="taskDisplay"
      >
        <a-radio-group v-model:value="formState.taskDisplay" class="market-setting-radio">
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
        <a-radio-group v-model:value="formState.drainageEnabled" class="market-setting-radio">
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
            accept="image/jpg, image/gif, image/png"
            :file-list="[]"
            :maxCount="1"
            list-type="picture-card"
            @change="uploadDrainageImage"
          >
            <img v-if="formState.drainageImage" :src="formState.drainageImage" alt="" style="width: 100%"/>
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
                <img src="../../../img/course-manage/base-setting/drainage-style.png" alt="drainage" style="height: 500px">
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
        <div class="flex justify-between items-center px-24 py-16 border-solid border-[#F0F0F0] border-t-0 border-x-0">
          <div class="text-16 text-[#1E2226] font-medium">{{ contractPreview.goodsName }}</div>
          <CloseOutlined class="h-16 w-16" @click="contractPreviewModalVisible = false"/>
        </div>
      </template>
      <div class="w-full flex flex-col space-y-32 p-32">
        <div class="flex items-end justify-between gap-4">
          <span class="flex-none whitespace-nowrap opacity-0">{{ `${ t('modal.contractNumber') }: ${contractPreview.code}` }}</span>
          <span class="grow text-center text-22 font-medium">{{ contractPreview.name }}</span>
          <span class="flex-none whitespace-nowrap text-gray-500">{{ `${ t('modal.contractNumber') }: ${contractPreview.code}` }}</span>
        </div>
        <div v-html="contractPreview.content" class="text-gray-500 contract-content"></div>
        <div class="flex space-x-64">
          <div class="flex-1 flex flex-col items-start justify-between space-y-22">
            <span class="text-18 font-medium">{{ `${ t('modal.partyA') }：` }}</span>
            <div class="w-full flex flex-col space-y-22">
              <img :src="contractPreview.seal" alt="" class="w-150 h-150"/>
              <div class="flex items-center">
                <span class="text-gray-500">{{ `${ t('modal.signingDate') }：` }}</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                  {{ contractPreview.signDate }}
                </div>
              </div>
            </div>
          </div>
          <div class="flex-1 flex flex-col items-start justify-between">
            <span class="text-18 font-medium">{{ `${ t('modal.partyB') }：` }}</span>
            <div class="w-full flex flex-col space-y-22">
              <div v-if="contractPreview.sign && contractPreview.sign.handSignature" class="flex items-center">
                <span class="text-gray-500">{{ `${ t('modal.handSignature') }：` }}</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                  <img :src="contractPreview.sign.handSignature" class="h-35" alt=""/>
                </div>
              </div>
              <div v-if="contractPreview.sign && contractPreview.sign.truename" class="flex items-center">
                <span class="text-gray-500">{{ `${ t('modal.partyBName') }：` }}</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                  {{ contractPreview.sign.truename }}
                </div>
              </div>
              <div v-if="contractPreview.sign && contractPreview.sign.IDNumber" class="flex items-center">
                <span class="text-gray-500">{{ `${ t('modal.iDNumber') }：` }}</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                  {{ contractPreview.sign.IDNumber }}
                </div>
              </div>
              <div v-if="contractPreview.sign && contractPreview.sign.phoneNumber" class="flex items-center">
                <span class="text-gray-500">{{ `${ t('modal.contactInformation') }：` }}</span>
                <div class="grow border-solid border-0 border-b border-gray-300 font-medium">
                  {{ contractPreview.sign.phoneNumber }}
                </div>
              </div>
              <div class="flex items-center">
                <span class="text-gray-500">{{ `${ t('modal.signingDate') }：` }}</span>
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
.market-setting-radio {
  .ant-radio-wrapper {
    font-weight: 400 !important;
  }
}

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
