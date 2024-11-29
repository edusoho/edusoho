<script setup>
import AntConfigProvider from '../../components/AntConfigProvider.vue';
import {ref, h, reactive, computed, watch} from 'vue';
import { PlusCircleOutlined, CloseOutlined, EditOutlined } from '@ant-design/icons-vue';
import { Empty } from 'ant-design-vue';
import dayjs from 'dayjs';
const simpleImage = Empty.PRESENTED_IMAGE_SIMPLE;
import draggable from 'vuedraggable';
import Api from '../../../api';

const props = defineProps({
  course: {required: true},
})

const lessons = ref([]);
const drawerType = ref();

const formRef = ref();
const baseFormState = reactive({
  title: null,
});
const baseRules = reactive({
  title: [
    { required: true, message: '请输入标题名称', trigger: 'blur' }
  ],
});
const formState = reactive({ ...baseFormState });
const rules = reactive({ ...baseRules });
const tagOptions = ref([]);
const searchParams = reactive({
  tag: null,
  keywordType: '1',
  keyword: null,
})

const updateFormItem = (drawerType) => {
  if (drawerType === 'replay') {
    Object.assign(formState, {
      ...baseFormState,
      playbackId: null,
      minutes: null,
      seconds: null,
    });
    Object.assign(rules, {
      ...baseRules,
    });
  } else if (drawerType === 'liveOpen') {
    Object.assign(formState, {
      ...baseFormState,
      startTime: null,
      length: null,
      replayEnable: true,
    });
    Object.assign(rules, {
      ...baseRules,
      startTime: [
        { required: true, message: '请设置直播开始时间', trigger: 'blur' }
      ],
      length: [
        { required: true, message: '请设置直播时长', trigger: 'blur' }
      ],
    });
  } else {
    Object.assign(formState, { ...baseFormState });
    Object.assign(rules, { ...baseRules });
  }
};

const isDrawerOpen = computed(() => {
  return drawerType.value === 'liveOpen' || drawerType.value === 'replay';
});

const labelColConfig = computed(() => ({
  span: drawerType.value === 'liveOpen' ? 4 : 3,
}));

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
  const nowPlus5Minutes = now.add(5, 'minute');
  if (!current) {
    return { disabledHours: () => range(0, 24), disabledMinutes: () => range(0, 60) };
  }
  const isSameDay = current.isSame(now, 'day');
  if (isSameDay) {
    return {
      disabledHours: () => range(0, nowPlus5Minutes.hour()),
      disabledMinutes: () => nowPlus5Minutes.hour() === current.hour() ? range(0, nowPlus5Minutes.minute()) : [],
    };
  }
  return {
    disabledHours: () => [],
    disabledMinutes: () => [],
  };
};

function formatter(value) {
  return Math.round(Number(value)) || '';
}
function parser(value) {
  return Math.round(Number(value.replace(/[^\d.]/g, '')));
}

const filterOption = (input, option) => {
  return option.value.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

const table = {
  columns: [
    {
      key: 'user',
      title: '直播课时名称',
    },
    {
      key: 'joinedChannel',
      title: '直播时长',
    },
    {
      key: 'joinTime',
      title: '主讲人',
    },
    {
      key: 'joinTime',
      title: '直播时间',
    },
    {
      key: 'operation',
      title: '操作',
      fixed: 'right',
    },
  ],
  loading: ref(false),
  openCourse: ref(),
};

const pagination = {
  current: 1,
  total: 0,
  pageSize: 6,
  onChange: (page, pageSize) => {
    pagination.current = page;
    pagination.pageSize = pageSize;
  },
};

async function findLesson() {
  lessons.value = await Api.openCourse.findLesson(props.course.id);
  console.log(lessons.value)
}

const handleReset = () => {
  formRef.value.resetFields();
  drawerType.value = null;
};

const saveBtnLoading = ref(false);
function handleSave() {
  return formRef.value.validate()
    .then( async () => {
      if (drawerType.value === 'replay') {

      }
      if (drawerType.value === 'liveOpen') {
        const params = {
          type: drawerType.value,
          title: formState.title,
          startTime: Date.parse(formState.startTime)/1000,
          length: formState.length,
          replayEnable: formState.replayEnable
        }
        saveBtnLoading.value = true;
        await Api.openCourse.createLesson(props.course.id, params);
      }
      saveBtnLoading.value = false;
      drawerType.value = null;
      await findLesson();
      console.log(table.openCourse.value)
    })
    .catch((error) => {

    });
}

watch(() => drawerType.value,  async (newType) => {
  await findLesson();
  updateFormItem(newType);
}, { immediate: true });
</script>
<template>
  <AntConfigProvider>
    <div class="flex flex-col py-24 px-32">
      <div class="flex justify-between items-center">
        <div class="text-16 font-medium text-black text-opacity-88">课时管理</div>
        <div class="flex space-x-20">
          <a-button type="primary" :icon="h(PlusCircleOutlined)" @click="drawerType = 'replay'">添加回放</a-button>
          <a-button type="primary" :icon="h(PlusCircleOutlined)" @click="drawerType = 'liveOpen'">添加直播</a-button>
        </div>
      </div>
      <div v-if="lessons.length === 0">
        <a-empty :image="simpleImage" class="mt-140"/>
      </div>
      <div v-else class="mt-20">
        <draggable
          v-model="lessons"
          item-key="id"
        >
          <template #item="{element, index}">
            <div class="flex items-center justify-between mb-12 bg-[#FAFAFA] rounded-8 px-14 py-20 cursor-grab">
              <div class="flex items-center space-x-12">
                <img src="../../../img/move-icon.png" class="w-16" draggable="false" alt="">
                <div v-if="element.status === 'unpublished'" class="px-8 h-22 leading-22 text-12 text-white font-normal bg-[#87898F] rounded-6">未发布</div>
                <div class="text-14 font-normal text-black">{{ `课时 ${index + 1} ：${element.title}（${element.length}）` }}</div>
              </div>
              <div class="flex items-center space-x-20 text-[--primary-color] text-14 font-normal">
                <div class="flex items-center"><EditOutlined class="mr-4"/>编辑</div>
                <div class="flex items-center">预览</div>
                <div class="flex items-center">发布</div>
                <div class="flex items-center">取消发布</div>
                <div class="flex items-center">删除</div>
              </div>
            </div>
          </template>
        </draggable>
      </div>
    </div>
    <a-drawer
      v-model:open="isDrawerOpen"
      placement="right"
      :closable="false"
      :maskClosable="false"
      :bodyStyle="{padding: 0}"
      width="900px"
    >
      <div class="fixed top-0 right-0 w-900 flex justify-between items-center px-20 py-14 border border-x-0 border-t-0 border-[#EFF0F5] border-solid bg-white">
        <div class="text-16 font-medium text-[#37393D]" v-if="drawerType === 'replay'">添加回放</div>
        <div class="text-16 font-medium text-[#37393D]" v-if="drawerType === 'liveOpen'">添加直播</div>
        <CloseOutlined class="text-16" @click="handleReset"/>
      </div>
      <a-form
        class="mt-53 px-20 py-24"
        ref="formRef"
        :model="formState"
        :rules="rules"
        :label-col="labelColConfig"
        autocomplete="off"
      >
        <a-form-item
          label="标题名称"
          name="title"
        >
          <a-input v-model:value="formState.title" placeholder="请输入" :allow-clear="true" show-count :maxlength="15" class="w-360"/>
          <div class="mt-4 text-12 font-normal text-[#87898F]">建议标题字数控制在15字以内，否则会影响手机端浏览</div>
        </a-form-item>
        <a-form-item
          v-if="drawerType === 'replay'"
          name="replayId"
          label="直播回放"
        >
          <a-form-item-rest>
            <div class="flex flex-col gap-y-16 p-24 border border-solid border-[#d9d9d9] rounded-8">
              <div class="flex gap-x-20">
                <a-select
                  v-model:value="searchParams.tag"
                  show-search
                  :allow-clear="true"
                  style="min-width: 160px; max-width: 160px"
                  placeholder="选择标签"
                  :options="tagOptions"
                  :filter-option="filterOption"
                ></a-select>
                <a-select
                  style="min-width: 106px; max-width: 106px"
                  v-model:value="searchParams.keywordType"
                  :allow-clear="true"
                >
                  <a-select-option value="1">直播名称</a-select-option>
                  <a-select-option value="2">主讲人</a-select-option>
                  <a-select-option value="3">课程名称</a-select-option>
                </a-select>
                <a-input v-model:value="searchParams.keyword" placeholder="请输入" :allow-clear="true"/>
                <a-button type="primary" ghost>搜索</a-button>
                <a-button>重置</a-button>
              </div>
              <div>222222</div>
              <div>333333</div>
            </div>
          </a-form-item-rest>
        </a-form-item>
        <a-form-item
          v-if="drawerType === 'replay'"
          label="直播回放时长"
        >
          <div>1111111111</div>
        </a-form-item>
        <a-form-item
          v-if="drawerType === 'liveOpen'"
          label="直播开始时间"
          name="startTime"
        >
          <a-date-picker v-model:value="formState.startTime" :disabled-date="disabledDate" :disabled-time="disabledTime" :show-time="{ format: 'HH:mm' }" :show-now="false" format="YYYY-MM-DD HH:mm" :allow-clear="true" placeholder="开始时间" class="w-268"/>
        </a-form-item>
        <a-form-item
          v-if="drawerType === 'liveOpen'"
          label="直播时长"
          name="length"
        >
          <a-input-number v-model:value="formState.length" :formatter="formatter" :parser="parser" addon-after="分" class="w-120"/>
        </a-form-item>
        <a-form-item
          v-if="drawerType === 'liveOpen'"
          label="是否允许观看回放"
          name="replayEnable"
        >
          <a-switch v-model:checked="formState.replayEnable" checked-children="允许" un-checked-children="不允许"/>
        </a-form-item>
      </a-form>
      <div class="fixed bottom-0 right-0 w-900 flex flex-row-reverse justify-between items-center px-20 py-14 border border-x-0 border-b-0 border-[#EFF0F5] border-solid bg-white">
        <div class="space-x-16">
          <a-button @click="handleReset">取消</a-button>
          <a-button type="primary" @click="handleSave" :loading="saveBtnLoading">保存</a-button>
        </div>
      </div>
    </a-drawer>
  </AntConfigProvider>
</template>
