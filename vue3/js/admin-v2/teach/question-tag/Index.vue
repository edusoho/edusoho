<script setup>
import Search from './Search.vue';
import Create from './Create.vue';
import {onBeforeUnmount, onMounted, reactive, ref} from 'vue';
import Api from '../../../../api';
import {formatDate} from 'vue3/js/common';
import {DownOutlined} from '@ant-design/icons-vue';
import AntConfigProvider from '../../../components/AntConfigProvider.vue';
import TagModal from './TagModal.vue';
import {createCustomRow} from '../../../customRow';

const loading = ref(false);
const table = reactive({
  list: [],
  sourceId: null,
  targetId: null,
})
const searchParams = reactive({
  name: null,
  status: null,
});

const customRow = createCustomRow(table)

const scrollY = ref(0);
const calculateScrollY = () => {
  const windowHeight = window.innerHeight;
  const otherHeight = 370;
  scrollY.value = windowHeight - otherHeight;
};
onMounted(() => {
  calculateScrollY();
  window.addEventListener('resize', calculateScrollY);
});
onBeforeUnmount(() => {
  window.removeEventListener('resize', calculateScrollY);
});


const columns = [
  {
    key: 'seq',
    title: '序号',
    dataIndex: 'seq',
    width: 80
  },
  {
    key: 'name',
    title: '标签类型名称',
    dataIndex: 'name',
    width: '35%',
    ellipsis: true,
  },
  {
    key: 'tagNum',
    title: '数量',
    dataIndex: 'tagNum',
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

const modalVisible = ref(false);
const editId = ref();

async function onSearch(params) {
  searchParams.name = params.name;
  searchParams.status = params.status;
  await searchTagGroup(searchParams)
}

async function onCreate(params) {
  await Api.questionTag.createTagGroup(params)
  await searchTagGroup(searchParams)
}

async function searchTagGroup(params) {
  loading.value = true;
  table.list = await Api.questionTag.searchTagGroup(params);
  loading.value = false;
}
searchTagGroup(searchParams);

async function updateTagGroupStatus(id, status) {
  const params = {
    status: status
  }
  await Api.questionTag.updateTagGroup(id, params);
  await searchTagGroup(searchParams);
}

async function deleteTagGroup(id) {
  Api.questionTag.deleteTagGroup(id);
  await searchTagGroup(searchParams);
}

function editTagGroup(id) {
  editId.value = id;
  modalVisible.value = true;
}
</script>

<template>
  <AntConfigProvider>
    <div class="flex flex-col p-16">
      <div class="text-18 leading-32 font-medium">题目标签管理</div>
      <a-divider class="mt-12 mb-20 border-2" />
      <Search
        class="mb-24"
        :is-group="true"
        @search="onSearch"
      />
      <Create
        class="mb-12"
        :is-group="true"
        @create="onCreate"
      />
      <a-table
        :columns="columns"
        :data-source="table.list"
        :row-key="record => record.id"
        :pagination="false"
        :loading="loading"
        :custom-row="customRow"
        :scroll="{ y: scrollY }"
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
                    <div @click="updateTagGroupStatus(record.id, 1)">启用</div>
                  </a-menu-item>
                  <a-menu-item>
                    <div @click="updateTagGroupStatus(record.id, 0)">禁用</div>
                  </a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>
          </template>
          <template v-if="column.key === 'operation'">
            <div class="gap-16 flex">
              <div class="cursor-pointer text-[--primary-color]" @click="editTagGroup(record.id)">管理</div>
              <a-popconfirm
                placement="bottomRight"
                ok-text="确定"
                cancel-text="取消"
                @confirm="deleteTagGroup(record.id)"
              >
                <template #title>
                  <div class="w-240">删除后相关题目将不再带有这个标签类型下的标签</div>
                </template>
                <div class="cursor-pointer text-[--primary-color]">删除</div>
              </a-popconfirm>
            </div>
          </template>
        </template>
      </a-table>
    </div>
    <tag-modal
      v-model:modal-visible="modalVisible"
      :edit-id="editId"
      @need-refresh="searchTagGroup(searchParams)"
    />
  </AntConfigProvider>
</template>

<style>
:deep(.ant-table-tbody > tr.target > td) {
  border-top: 1px solid #409eff;
  background-color: #d9ecff;
}
</style>

