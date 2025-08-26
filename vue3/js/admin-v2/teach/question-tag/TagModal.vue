<script setup>
import {reactive, ref, watch} from 'vue';
import Search from './Search.vue';
import Create from './Create.vue';
import Api from '../../../../api';
import {CheckOutlined, DownOutlined, EditOutlined} from '@ant-design/icons-vue';
import {formatDate} from 'vue3/js/common';
import {createCustomRow} from '../../../custom-row';
import {cloneDeep} from 'lodash';

const modalVisible = defineModel('modalVisible', { type: Boolean })
const emit = defineEmits(['needRefresh']);
const props = defineProps({
  editId: String,
})

const needReset = ref(false);

watch(modalVisible,async () => {
  if (modalVisible.value) {
    searchParams.groupId = props.editId;
    await searchTag(searchParams)
  } else {
    needReset.value = true;
  }
})

const loading = ref(false);
const table = reactive({
  list: [],
  sourceId: null,
  targetId: null,
})
const searchParams = reactive({
  groupId: null,
  name: null,
  status: null,
});
const customRow = ref();
customRow.value = createCustomRow(table, onSorted, { draggable: true })

async function onSorted(list, { movedItem, sourceIndex, targetIndex }) {
  if (sourceIndex === targetIndex) {
    return;
  }
  const ids = list.map(item => {
    return item.id
  })
  const params = {
    groupId: props.editId,
    ids: ids
  }
  await Api.questionTag.sortTag(params)
  await searchTag(searchParams)
}

const columns = [
  {
    key: 'seq',
    title: '序号',
    dataIndex: 'seq',
    width: 80
  },
  {
    key: 'name',
    title: '标签名称',
    dataIndex: 'name',
    width: '35%',
    ellipsis: true,
  },
  {
    key: 'createdTime',
    title: '创建时间',
    dataIndex: 'createdTime',

  },
  {
    key: 'status',
    title: '状态',
    dataIndex: 'status',
  },
  {
    key: 'operation',
    title: '操作',
  },
]

async function onSearch(params) {
  searchParams.name = params.name;
  searchParams.status = params.status;
  await searchTag(searchParams)
  if (!searchParams.name && !searchParams.status) {
    customRow.value = createCustomRow(table, onSorted, { draggable: true })
  } else {
    customRow.value = createCustomRow(table, onSorted, { draggable: false })
  }
}

async function searchTag(params) {
  loading.value = true;
  table.list = await Api.questionTag.searchTag(params);
  loading.value = false;
}

async function onCreate(params) {
  await Api.questionTag.createTag({
    groupId: props.editId,
    ...params
  })
  await searchTag(searchParams)
  emit('needRefresh')
}

async function updateTag(id, {name, status}) {
  const params = {};
  if (status !== undefined) {
    params.status = status;
  }
  if (name !== undefined) {
    params.name = name;
  }
  await Api.questionTag.updateTag(id, params);
  await searchTag(searchParams);
}

async function deleteTag(id) {
  await Api.questionTag.deleteTag(id);
  await searchTag(searchParams);
  emit('needRefresh')
}

const editableData = reactive({});
function edit(id) {
  editableData[id] = cloneDeep(table.list.filter(item => id === item.id)[0]);
}
async function save(id) {
  await updateTag(id, {name: editableData[id].name})
  delete editableData[id];
  await searchTag(searchParams);
}
</script>

<template>
  <a-modal
    v-model:open="modalVisible"
    title="题目标签"
    centered
    width="1216px"
    :footer="false"
    :maskClosable="false"
  >
    <div class="flex flex-col h-728">
      <Search
        class="mb-24"
        :is-group="false"
        v-model:need-reset="needReset"
        @search="onSearch"
      />
      <Create
        class="mb-12"
        :is-group="false"
        :group-id="editId"
        @create="onCreate"
      />
      <a-table
        :columns="columns"
        :data-source="table.list"
        :row-key="record => record.id"
        :pagination="false"
        :loading="loading"
        :custom-row="customRow"
        :scroll="{ y: 570 }"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'name'">
            <div v-if="editableData[record.id]" class="flex items-center justify-between gap-12">
              <a-input
                v-model:value="editableData[record.id].name"
                :maxlength="50"
                show-count
              />
              <CheckOutlined @click="save(record.id)"/>
            </div>
            <div v-else class="flex items-center justify-between group">
              <div class="truncate">{{ record.name }}</div>
              <div class="group-hover:inline-block tw-hidden">
                <EditOutlined @click="edit(record.id)"/>
              </div>
            </div>
          </template>
          <template v-if="column.key === 'createdTime'">
            {{ formatDate(record.createdTime) }}
          </template>
          <template v-if="column.key === 'status'">
            <a-dropdown placement="bottom" class="w-fit">
              <div class="flex items-center gap-12 cursor-pointer">
                <a-badge :color="record.status == true ? '#00B42A' : '#FF4D4F'" :text="record.status == true ? '启用' : '禁用'" />
                <DownOutlined class="text-[rgba(0,0,0,0.25)]"/>
              </div>
              <template #overlay>
                <a-menu>
                  <a-menu-item>
                    <div @click="updateTag(record.id, {status: 1})">启用</div>
                  </a-menu-item>
                  <a-menu-item>
                    <div @click="updateTag(record.id, {status: 0})">禁用</div>
                  </a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>
          </template>
          <template v-if="column.key === 'operation'">
            <a-popconfirm
              placement="rightTop"
              ok-text="确定"
              cancel-text="取消"
              @confirm="deleteTag(record.id)"
            >
              <template #title>
                <div class="w-240">删除后相关题目将不再带有这个标签</div>
              </template>
              <div class="cursor-pointer text-[--primary-color] w-fit">删除</div>
            </a-popconfirm>
          </template>
        </template>
      </a-table>
    </div>
  </a-modal>
</template>
