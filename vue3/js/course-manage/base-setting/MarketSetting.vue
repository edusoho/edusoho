<script setup>
import {reactive, ref, watch} from 'vue';
import {QuestionCircleOutlined} from '@ant-design/icons-vue';
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
  buyExpiryTime: props.manage.course.buyExpiryTime === '0' ? null : props.manage.course.buyExpiryTime,
  taskDisplay: props.manage.course.taskDisplay,
  drainageEnabled: props.manage.course.drainageEnabled,
});

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

const selectTags = reactive(Array(props.manage.serviceTags.length).fill(false));
const handleChange = (tag, checked) => {
  console.log(tag, checked);
};

if (props.manage.vipInstalled && props.manage.vipEnabled) {
  Object.assign(formState, { vipLevelId: props.manage.course.vipLevelId })
}

watch( () => formState.drainageEnabled, () => {
  console.log(formState.drainageEnabled)
})
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

      <a-form-item
      >
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

      </a-form-item>

      <a-form-item
        label="强制签署"
      >

      </a-form-item>

      <a-form-item
        label="加入截止日期"
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
        label="会员免费兑换"
        name="vipLevelId"
      >
        <a-select
          v-model:value="formState.vipLevelId"
          style="width: 250px"
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
          v-for="(tag, index) in props.manage.serviceTags"
          :key="tag"
          v-model:checked="selectTags[index]"
          @change="checked => handleChange(tag, checked)"
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

        </a-form-item>

        <a-form-item
          label="引流文案"
        >

        </a-form-item>

        <a-form-item
          label="引流页样式"
        >

        </a-form-item>
      </div>


    </a-form>
  </div>
</template>

<style lang="less">
.market-setting-radio {
  .ant-radio-wrapper {
    font-weight: 400 !important;
  }
}
</style>
