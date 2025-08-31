<script setup>
// const emit = defineEmits(['setCategorySuccess'])
import {onMounted, reactive, ref} from 'vue';
import Api from '../../../../../../api';

const modalVisible = defineModel();
const props = defineProps({
  id: {
    type: String,
    default: '',
  },
});

const courseName = ref();
const referCourse = ref([])
const state = reactive({
  selectedIds: [],
  loading: false,
});

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

async function onSearch() {
  state.loading = true
  referCourse.value = await Api.cloudResources.searchReferCourse();
  state.loading = false
}

function onBatchReplace() {

}

onMounted(async () => {
  await onSearch();
})
</script>

<template>
  <a-modal v-model:open="modalVisible"
           title="引用详情"
           :width="900"
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
        <a-button type="primary" @click="onBatchReplace">批量替换</a-button>
      </div>
      <a-table
        :columns="columns"
        :data-source="referCourse"
        :row-key="record => record.id"
        :pagination="false"
        :loading="state.loading"
        :scroll="{ y: 500 }"
      >

      </a-table>
      <div class="flex flex-row-reverse">
        <div>111111</div>
      </div>
    </div>
  </a-modal>
</template>
