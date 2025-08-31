<script setup>
// const emit = defineEmits(['setCategorySuccess'])
import {onMounted, reactive, ref} from 'vue';
import Api from '../../../../../../api';
import ResourceSubstitution from './ResourceSubstitution.vue';

const modalVisible = defineModel();
const props = defineProps({
  id: {
    type: Number,
  },
});

const courseName = ref();
const referCourse = ref([])
const state = reactive({
  selectedIds: [],
  loading: false,
});

const resourceSubstitutionModalVisible = ref(false)
function openResourceSubstitutionModal() {
  resourceSubstitutionModalVisible.value = true
}
function closeReferCourseModal() {
  modalVisible.value = false;
}
function openReferCourseModal() {
  modalVisible.value = false;
}

const columns = [
  {
    key: 'name',
    title: '课程名称',
    dataIndex: 'name',
    width: '50%',
    ellipsis: true,
  },
  {
    key: '该资源引用次数',
    title: '数量',
    dataIndex: 'num',
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
  referCourse.value = await Api.cloudResources.searchReferCourse();
  state.loading = false
}

function onBatchReplace() {
  closeReferCourseModal();
  openResourceSubstitutionModal();
}

function onSelectChange(selectedIds) {
  state.selectedIds = selectedIds;
}

function onShowSizeChange(current, pageSize) {
  console.log(current, pageSize);
}

onMounted(async () => {
  await onSearch();
})
</script>

<template>
  <a-modal v-model:open="modalVisible"
           title="引用详情"
           :width="900"
           :footer="null"
           centered
  >
    <div class="flex flex-col">
      <div class="flex items-center justify-between mb-12">
        <div class="flex items-center gap-24">
          <a-input
            v-model:value="courseName"
            placeholder="课程名称"
            class="w-200"
            allow-clear
            @change="onSearch"
          />
          <div v-if="state.selectedIds.length > 0">{{ `已选: ${state.selectedIds.length}` }}</div>
        </div>
        <a-button type="primary" @click="onBatchReplace" :disabled="state.selectedIds.length === 0">批量替换</a-button>
      </div>
      <a-table
        class="mb-16"
        :columns="columns"
        :data-source="referCourse"
        :row-key="record => record.id"
        :pagination="false"
        :loading="state.loading"
        :row-selection="{ selectedRowKeys: state.selectedIds, onChange: onSelectChange }"
        :scroll="{ y: 500 }"
      >

      </a-table>
      <div class="flex flex-row-reverse">
        <a-pagination
          v-model:current="pagination.current"
          v-model:pageSize="pagination.pageSize"
          :total="pagination.total"
          show-size-changer
          :show-total="total => `共 ${total} 条数据`"
          @showSizeChange="onShowSizeChange"
        />
      </div>
    </div>
  </a-modal>
  <ResourceSubstitution
    v-model="resourceSubstitutionModalVisible"
    :ids="state.selectedIds"
  />
</template>
