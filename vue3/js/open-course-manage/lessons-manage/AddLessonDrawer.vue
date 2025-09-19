<script setup>
import {CloseOutlined, EditOutlined, VideoCameraOutlined} from '@ant-design/icons-vue';
import {computed, reactive, ref, watch} from 'vue';
import Api from '../../../api';
import {message} from 'ant-design-vue';
import dayjs from 'dayjs';
import {formatDate} from '../../common';

const modalVisible = defineModel();
const emit = defineEmits(['save', 'cancel'])
const props = defineProps({
  drawerType: {
    type: String,
    default: null,
  },
  editId: {
    type: String,
    default: null,
  },
  courseId: {
    type: String,
    default: null,
  }
});

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

const labelColConfig = computed(() => ({
  span: props.drawerType === 'liveOpen' ? 4 : 3,
}));

const searchParams = reactive({
  replayTagId: null,
  keywordType: 'activityTitle',
  keyword: null,
})

function formatter(value) {
  return Math.round(Number(value)) || '';
}
function parser(value) {
  return Math.round(Number(value.replace(/[^\d.]/g, '')));
}

const filterOption = (input, option) => {
  return option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

const defaultPickerValue = ref();
const getDefaultPickerValue = () => {
  const now = new Date();
  now.setMinutes(now.getMinutes() + 5);
  defaultPickerValue.value = dayjs(now);
};

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

const pagination = {
  current: 1,
  pageSize: 6,
  total: 0,
  onChange: (page) => {
    pagination.current = page;
    searchReplay();
  },
};

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

function getLimitedText(text) {
  if (text.length <= 15) {
    return text;
  } else {
    return text.slice(0, 15);
  }
}

function onSelect(record) {
  formState.copyId = record.id;
  formState.replayId = record.replayId;
  formState.replayLength = record.liveSecond;
  formState.replayTitle = record.title;
  formState.title = formState.title ? formState.title : getLimitedText(record.title);
  formRef.value.validateFields(["replayId"]);
  formRef.value.validateFields(["title"]);
}

function onEdit() {
  formState.copyId = null;
  formState.replayId = null;
  formState.replayLength = null;
  formState.replayTitle = null;
  formState.replayIdValidateError = false;
}

function resetDrawer() {
  formState.editId = null;
  pagination.current = 1;
  emit('cancel')
}

const saveBtnLoading = ref(false);
function handleSave() {
  return formRef.value.validate()
    .then( async () => {
      saveBtnLoading.value = true;
      formState.replayIdValidateError = false;
      let params = {};
      if (props.drawerType === 'replay') {
        params = {
          type: props.drawerType,
          title: formState.title,
          copyId: formState.copyId,
          replayId: formState.replayId,
        }
      }
      if (props.drawerType === 'liveOpen') {
        params = {
          type: props.drawerType,
          title: formState.title,
          startTime: Date.parse(formState.startTime)/1000,
          length: formState.length,
          replayEnable: formState.replayEnable === true ? '1' : '0',
        }
      }
      const isEdit = !!formState.editId;
      try {
        if (isEdit) {
          await Api.openCourse.updateLesson(props.courseId, formState.editId, params);
        } else {
          await Api.openCourse.createLesson(props.courseId, params);
        }
      } finally {
        saveBtnLoading.value = false;
      }
      resetDrawer();
      message.success('保存成功');
      emit('save')
    })
    .catch((error) => {
      if (error?.errorFields?.some((field) => { return field.name.includes('replayId') })) {
        formState.replayIdValidateError = true;
      }
    });
}

function handleReset() {
  formRef.value.resetFields();
  formState.replayIdValidateError = false;
  searchParams.replayTagId = null;
  searchParams.keywordType = 'activityTitle';
  searchParams.keyword = null;
  replays.data = [];
  resetDrawer();
}

const replayTagOptions = ref([]);
async function fetchReplayTagOptions() {
  const tags = await Api.tag.getTag();
  replayTagOptions.value = tags.map(item => ({
    value: item.id,
    label: item.name,
  }));
}

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

async function getLiveOpenData() {
  const liveOpen = await Api.openCourse.getLesson(props.courseId, formState.editId);
  return {
    title: liveOpen.title,
    startTime: dayjs(formatDate(liveOpen.startTime, "YYYY-MM-DD HH:mm"), "YYYY-MM-DD HH:mm"),
    length: liveOpen.length / 60,
    replayEnable: liveOpen.replayEnable === '1',
  };
}

async function getReplayData() {
  const replay = await Api.openCourse.getLesson(props.courseId, formState.editId);
  return {
    title: replay.title,
    copyId: replay.copyId,
    replayId: replay.replayId,
    replayLength: Number(replay.length),
    replayTitle: replay.liveTitle,
  };
}

async function getExtraFormState(type, isEdit) {
  const stateMap = {
    replay: isEdit
      ? await getReplayData()
      : { copyId: null, replayId: null, replayTitle: null, replayLength: null, replayIdValidateError: false },
    liveOpen: isEdit
      ? await getLiveOpenData()
      : { startTime: null, length: 60, replayEnable: true },
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

watch(() => props.drawerType,async (newType) => {
  if (props.drawerType) {
    if (props.editId) {
      formState.editId = props.editId;
    }
    await updateFormItem(newType);
    getDefaultPickerValue();
  }
}, { immediate: true });
</script>

<template>
  <a-drawer
    v-model:open="modalVisible"
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
              :row-key="replay => replay.id"
              :loading="replays.loading"
              :pagination="false"
            >
              <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'title'">
                  <div class="truncate max-w-208 w-fit">{{ record.title }}</div>
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
                  <div class="text-[--primary-color] cursor-pointer" @click="onSelect(record)">选择</div>
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
                :showSizeChanger="false"
                @change="pagination.onChange"
              />
            </div>
          </div>
          <div v-else class="flex">
            <div class="flex px-16 py-8 bg-[#F5F5F5] rounded-6 mr-16">
              <VideoCameraOutlined class="w-16 mr-8"/>
              <div class="text-14 font-medium text-[#37393D] truncate max-w-208 w-fit">{{ formState.replayTitle }}</div>
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
        <a-date-picker v-model:value="formState.startTime" :default-picker-value="defaultPickerValue" :show-time="{ defaultValue: defaultPickerValue, format: 'HH:mm' }" :disabled-date="disabledDate" :disabled-time="disabledTime" :show-now="false" format="YYYY-MM-DD HH:mm" :allow-clear="true" placeholder="开始时间" class="w-268"/>
      </a-form-item>
      <a-form-item
        v-if="drawerType === 'liveOpen'"
        label="直播时长"
        name="length"
      >
        <a-input-number v-model:value="formState.length" min="1" max="480" :formatter="formatter" :parser="parser" addon-after="分" class="w-120"/>
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
</template>
