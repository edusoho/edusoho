<script setup>
import Search from './Search.vue';
import Create from './Create.vue';
import {onBeforeUnmount, onMounted, reactive, ref, watch} from 'vue';
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
    name: '序号',
    dataIndex: 'seq',
    width: 80
  },
  {
    key: 'name',
    title: '标签类型',
    dataIndex: 'name',
    width: '35%',
    ellipsis: true,
  },
  {
    key: 'num',
    title: '数量',
    dataIndex: 'num',
  },
  {
    key: 'createTime',
    title: '创建时间',
    dataIndex: 'createTime',

  },
  {
    key: 'state',
    title: '状态',
    dataIndex: 'state',
  },
  {
    key: 'operation',
    title: '操作',
  },
]

const modalVisible = ref(false);
const editId = ref();
const needRefresh = ref(false);

watch(modalVisible, async () => {
  if (!modalVisible && needRefresh) {
    await searchTagGroup();
  }
})

async function searchTagGroup(params) {
  loading.value = true;
  table.list = await Api.questionTag.searchTagGroup(params);
  loading.value = false;
}
searchTagGroup();

async function enableTag(id) {
  await Api.questionTag.enableTag(id);
  await searchTagGroup();
}
async function disableTag(id) {
  await Api.questionTag.disableTag(id);
  await searchTagGroup();
}

async function deleteTag(id) {
  await Api.questionTag.deleteTag(id);
  await searchTagGroup();
}

function editTag(id) {
  needRefresh.value = false;
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
        @search="searchTagGroup"
      />
      <Create
        class="mb-12"
        :is-group="true"
        @create="searchTagGroup"
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
          <template v-if="column.key === 'createTime'">
            {{ formatDate(record.createTime) }}
          </template>
          <template v-if="column.key === 'state'">
            <a-dropdown placement="bottom" class="w-fit">
              <div class="flex items-center gap-12 cursor-pointer">
                <a-badge :color="record.state === 'enable' ? '#00B42A' : '#FF4D4F'" :text="record.state === 'enable' ? '启用' : '禁用'" />
                <DownOutlined />
              </div>
              <template #overlay>
                <a-menu>
                  <a-menu-item>
                    <div @click="enableTag(record.id)">启用</div>
                  </a-menu-item>
                  <a-menu-item>
                    <div @click="disableTag(record.id)">禁用</div>
                  </a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>
          </template>
          <template v-if="column.key === 'operation'">
            <div class="gap-16 flex">
              <div class="cursor-pointer text-[--primary-color]" @click="editTag(record.id)">管理</div>
              <a-popconfirm
                placement="bottom"
                ok-text="确定"
                cancel-text="取消"
                @confirm="deleteTag(record.id)"
              >
                <template #title>
                  <div class="w-240">删除后该标签将被删除，相关题目将不再带有这个标签</div>
                </template>
                <div class="cursor-pointer text-[--primary-color]">删除</div>
              </a-popconfirm>
            </div>
          </template>
        </template>
      </a-table>
    </div>
    <tag-modal
      v-model:modalVisible="modalVisible"
      v-model:needRefresh="needRefresh"
      :editId="editId"
    />
  </AntConfigProvider>
</template>

<style>
:deep(.ant-table-tbody > tr.target > td) {
  border-top: 1px solid #409eff;
  background-color: #d9ecff;
}
</style>

