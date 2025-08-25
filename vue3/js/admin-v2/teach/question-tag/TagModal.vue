<script setup>
import {reactive, ref, watch} from 'vue';
import Search from './Search.vue';
import Create from './Create.vue';
import Api from '../../../../api';
import {DownOutlined} from '@ant-design/icons-vue';
import {formatDate} from 'vue3/js/common';
import {createCustomRow} from '../../../customRow';

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

const customRow = createCustomRow(table)

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

async function updateTagStatus(id, status) {
  const params = {
    status: status
  }
  await Api.questionTag.updateTag(id, params);
  await searchTag(searchParams);
}

async function deleteTag(id) {
  await Api.questionTag.deleteTag(id);
  await searchTag(searchParams);
  emit('needRefresh')
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
                    <div @click="updateTagStatus(record.id, 1)">启用</div>
                  </a-menu-item>
                  <a-menu-item>
                    <div @click="updateTagStatus(record.id, 0)">禁用</div>
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
