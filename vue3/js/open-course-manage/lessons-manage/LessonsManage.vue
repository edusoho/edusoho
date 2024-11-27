<script setup>
import AntConfigProvider from '../../components/AntConfigProvider.vue';
import {ref, h, reactive} from 'vue';
import { PlusCircleOutlined, CloseOutlined } from '@ant-design/icons-vue';
import { Empty } from 'ant-design-vue';
import dayjs from 'dayjs';
const simpleImage = Empty.PRESENTED_IMAGE_SIMPLE;

const lessons = ref([]);
const playbackDrawerVisible = ref(false);
const liveDrawerVisible = ref(false);

const formRef = ref();
const formState = reactive({
  title: null,
  startTime: null,
  duration: null,
  allowPlayback: true,
});
const rules = ref({
  title: [
    { required: true, message: '请输入试卷名称', trigger: 'blur' }
  ],
  startTime: [
    { required: true, message: '请设置直播开始时间', trigger: 'blur' }
  ],
  duration: [
    { required: true, message: '请设置直播时长', trigger: 'blur' }
  ],
});

const range = (start, end) => {
  const result = [];
  for (let i = start; i < end; i++) {
    result.push(i);
  }
  return result;
};
const disabledDate = (current) => {
  const now = dayjs();
  return current && current < now.startOf('day');
};
const disabledTime = (current) => {
  const now = dayjs();
  if (!current) {
    return { disabledHours: () => range(0, 24), disabledMinutes: () => range(0, 60) };
  }
  const isSameDay = current.isSame(now, 'day');
  if (isSameDay) {
    return {
      disabledHours: () => range(0, now.hour()),
      disabledMinutes: () => now.hour() === current.hour() ? range(0, now.minute()) : [],
    };
  }
  return {
    disabledHours: () => [],
    disabledMinutes: () => []
  };
};

function formatter(value) {
  return Math.round(Number(value)) || '';
}
function parser(value) {
  return Math.round(Number(value.replace(/[^\d.]/g, '')));
}

function addLive() {

}
</script>
<template>
  <AntConfigProvider>
    <div class="flex flex-col py-24 px-32">
      <div class="flex justify-between items-center">
        <div class="text-16 font-medium text-black text-opacity-88">课时管理</div>
        <div class="flex space-x-20">
          <a-button type="primary" :icon="h(PlusCircleOutlined)">添加回放</a-button>
          <a-button type="primary" :icon="h(PlusCircleOutlined)" @click="liveDrawerVisible = true">添加直播</a-button>
        </div>
      </div>
      <div v-if="lessons.length === 0">
        <a-empty :image="simpleImage" class="mt-140"/>
      </div>
      <div v-else>kjsbdkjasbdjksabdkjsabdaksjbdaskjbd,dsakbdsakhvdsakjhbdsakjndbsdbsamdvasjhdvas</div>
    </div>
    <a-drawer
      v-model:open="liveDrawerVisible"
      placement="right"
      :closable="false"
      :maskClosable="false"
      :bodyStyle="{padding: 0}"
      width="900px"
    >
      <div class="fixed top-0 right-0 w-900 flex justify-between items-center px-20 py-14 border border-x-0 border-t-0 border-[#EFF0F5] border-solid bg-white">
        <div class="text-16 font-medium text-[#37393D]">添加直播</div>
        <CloseOutlined class="text-16" @click="liveDrawerVisible = false"/>
      </div>
      <a-form
        class="mt-53 px-20 py-24"
        ref="formRef"
        :model="formState"
        :rules="rules"
        :label-col="{ span: 4 }"
        autocomplete="off"
      >
        <a-form-item
          label="标题名称"
          name="title"
        >
          <a-input v-model:value="formState.title" placeholder="请输入" :allowClear="true" show-count :maxlength="15" class="w-360"/>
          <div class="mt-4 text-12 font-normal text-[#87898F]">建议标题字数控制在15字以内，否则会影响手机端浏览</div>
        </a-form-item>
        <a-form-item
          label="直播开始时间"
          name="startTime"
        >
          <a-date-picker v-model:value="formState.startTime" :disabled-date="disabledDate" :disabled-time="disabledTime" :show-time="{ format: 'HH:mm' }" format="YYYY-MM-DD HH:mm" placeholder="开始时间" :allowClear="true" class="w-268"/>
        </a-form-item>
        <a-form-item
          label="直播时长"
          name="duration"
        >
          <a-input-number v-model:value="formState.duration" :formatter="formatter" :parser="parser" addon-after="分" class="w-120"/>
        </a-form-item>
        <a-form-item
          label="是否允许观看回放"
          name="allowPlayback"
        >
          <a-switch v-model:checked="formState.allowPlayback" checked-children="允许" un-checked-children="不允许"/>
        </a-form-item>
      </a-form>
      <div class="fixed bottom-0 right-0 w-900 flex flex-row-reverse justify-between items-center px-20 py-14 border border-x-0 border-b-0 border-[#EFF0F5] border-solid bg-white">
        <div class="space-x-16">
          <a-button @click="liveDrawerVisible = false">取消</a-button>
          <a-button type="primary" @click="addLive">保存</a-button>
        </div>
      </div>
    </a-drawer>
  </AntConfigProvider>
</template>

<style scoped lang="less">

</style>
