<script setup>
import AntConfigProvider from '../../components/AntConfigProvider.vue';
import {ref, h, reactive, computed, watch, createVNode} from 'vue';
import { PlusCircleOutlined, CloseOutlined, EditOutlined, EyeOutlined, SendOutlined, CloseCircleOutlined, DeleteOutlined, ExclamationCircleOutlined, VideoCameraOutlined } from '@ant-design/icons-vue';
import {Empty, message, Modal} from 'ant-design-vue';
import dayjs from 'dayjs';
const simpleImage = Empty.PRESENTED_IMAGE_SIMPLE;
import draggable from 'vuedraggable';
import Api from '../../../api';
import {formatDate, goto} from '../../common';

const props = defineProps({
  course: {required: true},
})

const lessons = reactive({
  loading: false,
  data: [],
  ids: [],
});

async function fetchLessons() {
  lessons.loading = true;
  lessons.data = await Api.openCourse.fetchLessons(props.course.id);
  lessons.ids = lessons.data.map(item => item.id);
  lessons.loading = false;
}
fetchLessons();

const drawerType = ref();
const formRef = ref();
const baseFormState = reactive({
  title: null,
  editId: null,
});
const baseRules = reactive({
  title: [
    { required: true, message: '请输入标题名称', trigger: 'blur' }
  ],
});
const formState = reactive({ ...baseFormState });
const rules = reactive({ ...baseRules });

async function updateFormItem(drawerType) {
  Object.keys(formState).forEach((key) => key !== "editId" && delete formState[key]);
  Object.keys(rules).forEach((key) => delete rules[key]);

  if (!drawerType) {
    Object.assign(formState, baseFormState);
    Object.assign(rules, baseRules);
    return;
  }

  const isEdit = !!formState.editId;
  const [extraFormState, validationRules] = await Promise.all([
    getExtraFormState(drawerType, isEdit),
    getValidationRules(drawerType),
  ]);

  Object.assign(formState, { ...baseFormState, ...extraFormState, editId: formState.editId });
  Object.assign(rules, { ...baseRules, ...validationRules });

  if (drawerType === "replay") {
    await fetchReplayTagOptions();
    await searchReplay();
  }
}

async function getExtraFormState(type, isEdit) {
  const stateMap = {
    replay: isEdit
      ? await getReplayData()
      : { copyId: null, replayId: null, replayTitle: null, replayLength: null, replayIdValidateError: false },
    liveOpen: isEdit
      ? await getLiveOpenData()
      : { startTime: null, length: null, replayEnable: true },
  };
  return stateMap[type] || {};
}

function getValidationRules(type) {
  const rulesMap = {
    replay: {
      replayId: [{ required: true, message: "请选择直播回放", trigger: "blur" }],
    },
    liveOpen: {
      startTime: [{ required: true, message: "请设置直播开始时间", trigger: "blur" }],
      length: [{ required: true, message: "请设置直播时长", trigger: "blur" }],
    },
  };
  return rulesMap[type] || {};
}

async function getLiveOpenData() {
  const liveOpen = await Api.openCourse.getLesson(props.course.id, formState.editId);
  return {
    title: liveOpen.title,
    startTime: dayjs(formatDate(liveOpen.startTime, "YYYY-MM-DD HH:mm"), "YYYY-MM-DD HH:mm"),
    length: liveOpen.length / 60,
    replayEnable: null,
  };
}

async function getReplayData() {
  const replay = await Api.openCourse.getLesson(props.course.id, formState.editId);
  return {
    title: replay.title,
    copyId: replay.copyId,
    replayId: replay.replayId,
    replayLength: Number(replay.length),
    replayTitle: replay.liveTitle,
  };
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
  return option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

async function changeLessonSort() {
  const newIds = lessons.data.map(item => item.id);
  if (lessons.ids.every((value, index) => value === newIds[index])) {
    return;
  }
  await Api.openCourse.changeLessonSort(props.course.id, { ids: newIds });
  await fetchLessons();
}

const replays = reactive({
  loading: false,
  columns: [
    {
      key: 'title',
      title: '直播课时名称',
    },
    {
      key: 'liveTime',
      title: '直播时长',
    },
    {
      key: 'anchor',
      title: '主讲人',
    },
    {
      key: 'liveStartTime',
      title: '直播时间',
    },
    {
      key: 'operation',
      title: '操作',
    },
  ],
  data: [],
});

const searchParams = reactive({
  replayTagId: null,
  keywordType: 'activityTitle',
  keyword: null,
})

const pagination = {
  current: 1,
  pageSize: 6,
  total: 0,
  onChange: (page) => {
    pagination.current = page;
    searchReplay();
  },
};

const replayTagOptions = ref([]);
async function fetchReplayTagOptions() {
  const tags = await Api.tag.fetchReplayTag();
  replayTagOptions.value = tags.map(item => ({
    value: item.id,
    label: item.name,
  }));
}

async function searchReplay() {
  const searchQuery = {replayTagId: searchParams.replayTagId, keywordType: searchParams.keywordType, keyword: searchParams.keyword, offset: (pagination.current - 1) * pagination.pageSize, limit: pagination.pageSize};
  replays.loading = true;
  const { data, paging } = await Api.liveReplay.searchLiveReplay(searchQuery);
  pagination.total = paging.total;
  replays.loading = false;
  replays.data = data;
}

async function onSearch() {
  pagination.current = 1;
  await searchReplay();
}

async function onReset() {
  pagination.current = 1;
  searchParams.replayTagId = null;
  searchParams.keywordType = 'activityTitle';
  searchParams.keyword = null;
  await searchReplay();
}

function onSelect(copyId, replayId, replayLength, replayTitle) {
  formState.copyId = copyId;
  formState.replayId = replayId;
  formState.replayLength = replayLength;
  formState.replayTitle = replayTitle;
  formRef.value.validateFields(["replayId"]);
}

function onEdit() {
  formState.copyId = null;
  formState.replayId = null;
  formState.replayLength = null;
  formState.replayTitle = null;
  formState.replayIdValidateError = false;
}

function editLesson(lessonType, id = null) {
  if (lessons.data.length >= 300 && !id) {
    message.error('最多可添加300个课时');
    return;
  }
  drawerType.value = lessonType;
  formState.editId = id;
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
      await fetchLessons();
    }
  });
}

async function publishLesson(id) {
  await Api.openCourse.publishLesson(props.course.id, id);
  message.success('发布成功');
  await fetchLessons();
}

async function unpublishLesson(id) {
  await Api.openCourse.unpublishLesson(props.course.id, id);
  message.success('取消发布成功');
  await fetchLessons();
}

function viewLesson(courseId, id) {
  goto(`/open/course/${courseId}/lesson/${id}/learn?as=preview`)
}

function resetDrawer() {
  drawerType.value = null;
  formState.editId = null;
}

function handleReset() {
  formRef.value.resetFields();
  formState.replayIdValidateError = false;
  searchParams.replayTagId = null;
  searchParams.keywordType = 'activityTitle';
  searchParams.keyword = null;
  pagination.current = 1;
  replays.data = [];
  resetDrawer();
}

const saveBtnLoading = ref(false);
function handleSave() {
  return formRef.value.validate()
    .then( async () => {
      saveBtnLoading.value = true;
      formState.replayIdValidateError = false;
      let params = {};
      if (drawerType.value === 'replay') {
        params = {
          type: drawerType.value,
          title: formState.title,
          copyId: formState.copyId,
          replayId: formState.replayId,
        }
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
      const isEdit = !!formState.editId;
      try {
        if (isEdit) {
          await Api.openCourse.updateLesson(props.course.id, formState.editId, params);
        } else {
          await Api.openCourse.createLesson(props.course.id, params);
        }
      } finally {
        saveBtnLoading.value = false;
      }
      resetDrawer();
      message.success('保存成功');
      await fetchLessons();
    })
    .catch((error) => {
      if (error.errorFields.some((field) => { return field.name.includes('replayId') })) {
        formState.replayIdValidateError = true;
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
      <a-spin :spinning="lessons.loading" tip="加载中...">
        <div v-if="lessons.data.length === 0">
          <a-empty :image="simpleImage" class="mt-140"/>
        </div>
        <div v-else-if="lessons.data.length > 0" class="mt-20">
          <draggable
            v-model="lessons.data"
            item-key="id"
            @end="changeLessonSort"
          >
            <template #item="{element, index}">
              <div class="flex items-center justify-between mb-12 bg-[#FAFAFA] rounded-8 px-14 py-20 cursor-grab">
                <div class="flex items-center space-x-12">
                  <img src="../../../img/move-icon.png" class="w-16" draggable="false" alt="">
                  <div v-if="element.status === 'unpublished'" class="px-8 h-22 leading-22 text-12 text-white font-normal bg-[#87898F] rounded-6">未发布</div>
                  <div class="text-14 font-normal text-black">{{`课时 ${index + 1} ：${element.title}（${Math.floor(Number(element.length / 60))}:${(Number(element.length) % 60) < 10 ? `0${Number(element.length) % 60}` : Number(element.length) % 60 }）` }}</div>
                </div>
                <div class="flex items-center space-x-20 text-[--primary-color] text-14 font-normal">
                  <div v-if="element.editable" @click="editLesson(element.type, element.id)" class="flex items-center cursor-pointer"><EditOutlined class="mr-4"/>编辑</div>
                  <div @click="viewLesson(props.course.id, element.id)" class="flex items-center cursor-pointer"><EyeOutlined class="mr-4"/>预览</div>
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
      <div class="fixed z-10 top-0 right-0 w-900 flex justify-between items-center px-20 py-14 border border-x-0 border-t-0 border-[#EFF0F5] border-solid bg-white">
        <div class="text-16 font-medium text-[#37393D]" v-if="drawerType === 'replay'">添加回放</div>
        <div class="text-16 font-medium text-[#37393D]" v-if="drawerType === 'liveOpen'">添加直播</div>
        <CloseOutlined class="text-16" @click="handleReset"/>
      </div>
      <a-form
        class="mt-53 mb-61 px-20 py-24"
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
            <div v-if="!formState.replayId" class="flex flex-col gap-y-16 p-24 border border-solid border-[#d9d9d9] rounded-8" :class="{ 'border-[#f53f3f]': formState.replayIdValidateError }">
              <div class="flex gap-x-20">
                <a-select
                  v-model:value="searchParams.replayTagId"
                  show-search
                  :allow-clear="true"
                  style="min-width: 160px; max-width: 160px"
                  placeholder="选择标签"
                  :options="replayTagOptions"
                  :filter-option="filterOption"
                ></a-select>
                <a-select
                  style="min-width: 106px; max-width: 106px"
                  v-model:value="searchParams.keywordType"
                  :allow-clear="true"
                >
                  <a-select-option value="activityTitle">直播名称</a-select-option>
                  <a-select-option value="anchor">主讲人</a-select-option>
                  <a-select-option value="courseTitle">课程名称</a-select-option>
                </a-select>
                <a-input v-model:value="searchParams.keyword" placeholder="请输入" :allow-clear="true"/>
                <a-button type="primary" ghost @click="onSearch">搜索</a-button>
                <a-button @click="onReset">重置</a-button>
              </div>
              <a-table
                :columns="replays.columns"
                :data-source="replays.data"
                :row-key="replay => replay.replayId"
                :loading="replays.loading"
                :pagination="false"
              >
                <template #bodyCell="{ column, record }">
                  <template v-if="column.key === 'title'">
                    <div class="truncate">{{ record.title }}</div>
                  </template>
                  <template v-if="column.key === 'liveTime'">
                    {{ `${(record.liveSecond / 60) % 1 === 0 ? record.liveSecond / 60 : parseFloat((record.liveSecond / 60).toFixed(1))} 分钟` }}
                  </template>
                  <template v-if="column.key === 'anchor'">
                    <div class="truncate">{{ record.anchor }}</div>
                  </template>
                  <template v-if="column.key === 'liveStartTime'">
                    {{ record.liveStartTime }}
                  </template>
                  <template v-if="column.key === 'operation'">
                    <div class="text-[--primary-color] cursor-pointer" @click="onSelect(record.id, record.replayId, record.liveSecond, record.title)">选择</div>
                  </template>
                </template>
              </a-table>
              <div class="flex flex-row-reverse">
                <a-pagination
                  size="small"
                  :disabled="pagination.total === 0"
                  v-model:current="pagination.current"
                  :total="pagination.total"
                  :page-size="pagination.pageSize"
                  @change="pagination.onChange"
                />
              </div>
            </div>
            <div v-else class="flex">
              <div class="flex px-16 py-8 bg-[#F5F5F5] rounded-6 mr-16">
                <VideoCameraOutlined class="w-16 mr-8"/>
                <div class="text-14 font-medium text-[#37393D]">{{ formState.replayTitle }}</div>
              </div>
              <div class="flex items-center cursor-pointer" @click="onEdit">
                <EditOutlined class="text-[#5E6166] w-16 mr-4"/>
                <div class="text-14 font-normal text-[#5E6166]">编辑</div>
              </div>
            </div>
          </a-form-item-rest>
        </a-form-item>
        <a-form-item
          v-if="formState.replayId"
          label="直播回放时长"
        >
          <div class="text-14 font-normal text-[#37393D]">{{ `${Math.floor(formState.replayLength / 60)} 分 ${formState.replayLength % 60 !== 0 ? formState.replayLength % 60 : '00'} 秒` }}</div>
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
      <div class="fixed z-10 bottom-0 right-0 w-900 flex flex-row-reverse justify-between items-center px-20 py-14 border border-x-0 border-b-0 border-[#EFF0F5] border-solid bg-white">
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
