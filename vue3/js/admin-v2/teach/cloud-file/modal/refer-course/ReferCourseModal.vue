<script setup>
import {reactive, ref, watch} from 'vue';
import Api from '../../../../../../api';
import ReplaceUploadFileModal from './ReplaceUploadFileModal.vue';

const modalVisible = defineModel();
const props = defineProps({
  fileId: {
    type: Number,
    default: 0,
  },
  fileType: {
    type: String,
    default: '',
  },
  filename: {
    type: String,
    default: '',
  },
  fileLength: {
    type: Number,
    default: 0,
  },
});

const courseSetTitle = ref('');
const referCourse = ref([])
const state = reactive({
  selectedCourseSetIds: [],
  loading: false,
});

watch(() => modalVisible.value, async (newValue) => {
  if (newValue) {
    await onSearch();
  }
})

watch(courseSetTitle, async () => {
  await onSearch();
})

const resourceSubstitutionModalVisible = ref(false)
function openResourceSubstitutionModal() {
  resourceSubstitutionModalVisible.value = true
}
function closeReferCourseModal() {
  modalVisible.value = false;
}
function openReferCourseModal() {
  modalVisible.value = true;
}

const columns = [
  {
    key: 'courseSetTitle',
    title: '课程名称',
    dataIndex: 'courseSetTitle',
    width: '50%',
    ellipsis: true,
  },
  {
    key: 'usedCount',
    title: '该资源引用次数',
    dataIndex: 'usedCount',
    width: '50%',
  },
]

const pagination = reactive({
  current: 1,
  total: 0,
  pageSize: 10,
});

async function onSearch() {
  state.loading = true
  const params = {
    offset: (pagination.current - 1) * pagination.pageSize,
    limit: pagination.pageSize,
    courseSetTitle: courseSetTitle.value
  }
  const {data, paging} = await Api.file.getFileUsage(props.fileId, params);
  referCourse.value = data;
  const {total, offset, limit} = paging;
  pagination.total = Number(total);
  pagination.pageSize = Number(limit);
  state.loading = false
}

function onBatchReplace() {
  closeReferCourseModal();
  openResourceSubstitutionModal();
}

function onSelectChange(selectedIds) {
  state.selectedCourseSetIds = selectedIds;
}

function onPaginationChange(current, pageSize) {
  pagination.current = current;
  pagination.pageSize = pageSize;
  onSearch();
}

function onReset() {
  courseSetTitle.value = '';
  referCourse.value = [];
  state.selectedCourseSetIds = [];
  state.loading = false;
  pagination.current = 1;
  pagination.total = 0;
  pagination.pageSize = 10;
}

function onCancel() {
  onReset()
  closeReferCourseModal();
}
</script>

<template>
  <a-modal v-model:open="modalVisible"
           title="引用详情"
           :width="900"
           :footer="null"
           :keyboard="false"
           :maskClosable="false"
           centered
           :focus-lock="false"
           @cancel="onCancel"
  >
    <div class="flex flex-col">
      <div class="flex items-center justify-between mb-12">
        <div class="flex items-center gap-24">
          <a-input
            v-model:value="courseSetTitle"
            placeholder="课程名称"
            class="w-200"
            allow-clear
            @change="onSearch"
          />
          <div v-if="state.selectedCourseSetIds.length > 0">{{ `已选: ${state.selectedCourseSetIds.length}` }}</div>
        </div>
        <a-button type="primary" @click="onBatchReplace" :disabled="state.selectedCourseSetIds.length === 0">批量替换</a-button>
      </div>
      <a-table
        class="mb-16 min-h-436"
        :columns="columns"
        :data-source="referCourse"
        :row-key="record => record.courseSetId"
        :pagination="false"
        :loading="state.loading"
        :row-selection="{ selectedRowKeys: state.selectedCourseSetIds, onChange: onSelectChange }"
        :scroll="{ y: 380 }"
      >

      </a-table>
      <div class="flex flex-row-reverse">
        <a-pagination
          v-model:current="pagination.current"
          v-model:pageSize="pagination.pageSize"
          :total="pagination.total"
          show-size-changer
          :show-total="total => `共 ${total} 条数据`"
          @showSizeChange="onPaginationChange"
          @change="onPaginationChange"
        />
      </div>
    </div>
  </a-modal>
  <ReplaceUploadFileModal
    v-model="resourceSubstitutionModalVisible"
    :courseSetIds="state.selectedCourseSetIds"
    :file-id="fileId"
    :file-type="fileType"
    :filename="filename"
    :file-length="fileLength"
    @cancel="openReferCourseModal"
    @ok="onReset"
  />
</template>
