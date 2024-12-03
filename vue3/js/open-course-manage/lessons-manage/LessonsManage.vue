<script setup>
import AntConfigProvider from '../../components/AntConfigProvider.vue';
import {ref, h, reactive, computed, watch, createVNode} from 'vue';
import { PlusCircleOutlined, CloseOutlined, EditOutlined, EyeOutlined, SendOutlined, CloseCircleOutlined, DeleteOutlined, ExclamationCircleOutlined } from '@ant-design/icons-vue';
import {Empty, message, Modal} from 'ant-design-vue';
import dayjs from 'dayjs';
const simpleImage = Empty.PRESENTED_IMAGE_SIMPLE;
import draggable from 'vuedraggable';
import Api from '../../../api';

const props = defineProps({
  course: {required: true},
})

const lessons = {
  loading: ref(false),
  data: ref([]),
};
const drawerType = ref();
const editId = ref();

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

async function updateFormItem(drawerType) {
  Object.keys(formState).forEach((key) => delete formState[key]);
  Object.keys(rules).forEach((key) => delete rules[key]);
  if (!drawerType) {
    Object.assign(formState, { ...baseFormState });
    Object.assign(rules, { ...baseRules });
    return;
  }
  const isEdit = !editId.value;
  const typeConfig = {
    replay: async () => {
      Object.assign(formState, {
        ...baseFormState,
        ...(isEdit ? { replayId: null } : {}),
      });
      Object.assign(rules, {
        ...baseRules,
        replayId: [{ required: true, message: '请选择直播回放', trigger: 'blur' }],
      });
    },
    liveOpen: async () => {
      const extraFormState = isEdit
        ? { startTime: null, length: null, replayEnable: true }
        : await getLiveOpenData();
      Object.assign(formState, { ...baseFormState, ...extraFormState });
      Object.assign(rules, {
        ...baseRules,
        startTime: [{ required: true, message: '请设置直播开始时间', trigger: 'blur' }],
        length: [{ required: true, message: '请设置直播时长', trigger: 'blur' }],
      });
    },
  };
  await typeConfig[drawerType]?.();
  async function getLiveOpenData() {
    const lesson = await Api.openCourse.getLesson(props.course.id, editId.value);
    return {
      title: lesson.title,
      startTime: null,
      length: null,
      replayEnable: null,
    };
  }
}

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

const replays = {
  loading: ref(false),
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
  data: ref([]),
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

function resetDrawer() {
  drawerType.value = null;
  editId.value = null;
}

function editLesson(lessonType, id = null) {
  if (lessons.data.value.length >= 300 && !id) {
    message.error('最多可添加300个课时');
    return;
  }
  drawerType.value = lessonType;
  editId.value = id;
}

function deleteLesson(id) {
  Modal.confirm({
    title: '确定要删除该课时吗？',
    icon: createVNode(ExclamationCircleOutlined),
    okText: '删除',
    cancelText: '取消',
    async onOk() {
      await Api.openCourse.deleteLesson(props.course.id, id);
      message.success('删除成功');
      await findLessons();
    }
  });
}

async function publishLesson(id) {
  await Api.openCourse.publishLesson(props.course.id, id);
  message.success('发布成功');
  await findLessons();
}

async function unpublishLesson(id) {
  await Api.openCourse.unpublishLesson(props.course.id, id);
  message.success('取消发布成功');
  await findLessons();
}

function viewLesson(id) {

}

async function findLessons() {
  lessons.loading.value = true;
  lessons.data.value = await Api.openCourse.findLessons(props.course.id);
  lessons.loading.value = false;
}
findLessons();

function handleReset() {
  formRef.value.resetFields();
  replayIdValidateError.value = false;
  replays.data.value = [];
  resetDrawer();
}

const replayIdValidateError = ref(false);
const saveBtnLoading = ref(false);
function handleSave() {
  return formRef.value.validate()
    .then( async () => {
      saveBtnLoading.value = true;
      replayIdValidateError.value = false;
      let params = {};
      if (drawerType.value === 'replay') {

      }
      if (drawerType.value === 'liveOpen') {
        params = {
          type: drawerType.value,
          title: formState.title,
          startTime: Date.parse(formState.startTime)/1000,
          length: formState.length,
          replayEnable: formState.replayEnable
        }
      }
      await Api.openCourse.createLesson(props.course.id, params);
      saveBtnLoading.value = false;
      resetDrawer();
      message.success('保存成功');
      await findLessons();
    })
    .catch((error) => {
      if (error.errorFields.some((field) => { return field.name.includes('replayId') })) {
        replayIdValidateError.value = true;
      }
    });
}

watch(() => drawerType.value,async (newType) => {
  await updateFormItem(newType);
}, { immediate: true });
</script>
<template>
  <AntConfigProvider>
    <div class="flex flex-col py-24 px-32">
      <div class="flex justify-between items-center">
        <div class="text-16 font-medium text-black text-opacity-88">课时管理</div>
        <div class="flex space-x-20">
          <a-button type="primary" :icon="h(PlusCircleOutlined)" @click="editLesson('replay')">添加回放</a-button>
          <a-button type="primary" :icon="h(PlusCircleOutlined)" @click="editLesson('liveOpen')">添加直播</a-button>
        </div>
      </div>
      <a-spin :spinning="lessons.loading.value" tip="加载中..." class="mt-140">
        <div v-if="lessons.data.value.length === 0 && lessons.loading.value === false">
          <a-empty :image="simpleImage" class="mt-140"/>
        </div>
        <div v-else-if="lessons.data.value.length > 0 && lessons.loading.value === false" class="mt-20">
          <draggable
            v-model="lessons.data.value"
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
                  <div @click="editLesson(element.type, element.id)" class="flex items-center cursor-pointer"><EditOutlined class="mr-4"/>编辑</div>
                  <div @click="viewLesson(element.id)" class="flex items-center cursor-pointer"><EyeOutlined class="mr-4"/>预览</div>
                  <div v-if="element.status === 'unpublished'" @click="publishLesson(element.id)" class="flex items-center cursor-pointer"><SendOutlined class="mr-4"/>发布</div>
                  <div v-if="element.status === 'published'" @click="unpublishLesson(element.id)" class="flex items-center cursor-pointer"><CloseCircleOutlined class="mr-4"/>取消发布</div>
                  <div v-if="element.status === 'unpublished'" @click="deleteLesson(element.id)" class="flex items-center cursor-pointer"><DeleteOutlined class="mr-4"/>删除</div>
                </div>
              </div>
            </template>
          </draggable>
        </div>
      </a-spin>
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
          :validate-status="'success'"
          class="open-course-lesson-replay-id-field"
        >
          <a-form-item-rest>
            <div class="flex flex-col gap-y-16 p-24 border border-solid border-[#d9d9d9] rounded-8" :class="{ 'border-[#f53f3f]': replayIdValidateError }">
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

<style lang="less">
.open-course-lesson-replay-id-field {
  .ant-form-item-explain-success {
    color: #f53f3f
  }
}
</style>
