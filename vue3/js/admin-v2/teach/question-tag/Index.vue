<script setup>
import Search from './Search.vue';
import Create from './Create.vue';
import {ref, watch} from 'vue';
import Api from '../../../../api';
import {formatDate} from 'vue3/js/common';
import {DownOutlined} from '@ant-design/icons-vue';
import AntConfigProvider from '../../../components/AntConfigProvider.vue';
import TagModal from './TagModal.vue';

const loading = ref(false);
const tagList = ref([]);

const tagListColumns = [
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
    await fetchTag();
  }
})

async function fetchTag(params) {
  loading.value = true;
  tagList.value = await Api.questionTag.search(params);
  loading.value = false;
}
fetchTag();

async function onSearch(params) {
  await fetchTag(params)
}

async function onCreate(params) {
  await Api.questionTag.createTag(params);
  await fetchTag(params)
}

async function enableTag(id) {
  await Api.questionTag.enableTag(id);
  await fetchTag();
}
async function disableTag(id) {
  await Api.questionTag.disableTag(id);
  await fetchTag();
}

async function deleteTag(id) {
  await Api.questionTag.deleteTag(id);
  await fetchTag();
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
        @search="onSearch"
      />
      <Create
        class="mb-12"
        @create="onCreate"
      />
      <a-table
        :columns="tagListColumns"
        :data-source="tagList"
        :row-key="record => record.id"
        :pagination="false"
        :loading="loading"
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
