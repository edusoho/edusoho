<script setup>
import Search from './Search.vue';
import Create from './Create.vue';
import {onBeforeUnmount, onMounted, reactive, ref, watch} from 'vue';
import Api from '../../../../api';
import {formatDate} from 'vue3/js/common';
import {DownOutlined, EditOutlined, CheckOutlined} from '@ant-design/icons-vue';
import AntConfigProvider from '../../../components/AntConfigProvider.vue';
import TagModal from './TagModal.vue';
import {createCustomRow} from '../../../custom-row';
import {cloneDeep} from 'lodash';

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
const customRow = ref();

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

async function onSorted(list, { movedItem, sourceIndex, targetIndex }) {
  if (sourceIndex === targetIndex) {
    return;
  }
  const ids = list.map(item => {
    return item.id
  })
  const params = {
    ids: ids
  }
  await Api.questionTag.sortTagGroup(params)
  await searchTagGroup(searchParams)
}

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

async function updateTagGroup(id, {name, status}) {
  const params = {};
  if (status !== undefined) {
    params.status = status;
  }
  if (name !== undefined) {
    params.name = name;
  }
  await Api.questionTag.updateTagGroup(id, params);
  await searchTagGroup(searchParams);
}

async function deleteTagGroup(id) {
  Api.questionTag.deleteTagGroup(id);
  await searchTagGroup(searchParams);
}

function openCreateModal() {
  modalVisible.value = true;
}

function reviewTagGroup(id) {
  editId.value = id;
  openCreateModal();
}

const editableData = reactive({});
function edit(id) {
  editableData[id] = cloneDeep(table.list.filter(item => id === item.id)[0]);
}
async function save(id) {
  const originalName = table.list.find(item => item.id === id)?.name;
  const newName = editableData[id].name;
  if (newName === originalName) {
    delete editableData[id];
    return;
  }
  await updateTagGroup(id, {name: editableData[id].name})
  delete editableData[id];
  await searchTagGroup(searchParams);
}

function genRules(record) {
  return {
    name: [
      {
        validator: async (_, value) => {
          const originalName = record.name;
          if (!value) {
            return Promise.reject("标签类型名称不能为空");
          }
          if (value === originalName) {
            return Promise.resolve();
          }
          const res = await Api.questionTag.isGroupNameExists({ name: value });
          if (!res.ok) {
            return Promise.reject("标签类型名称不得重复");
          }
          return Promise.resolve();
        },
        trigger: "blur",
      }
    ]
  };
}

watch([searchParams, editableData], ([newSearchParams, newEditableData]) => {
  if (!newSearchParams.name && !newSearchParams.status && Object.keys(newEditableData).length === 0) {
    customRow.value = createCustomRow(table, onSorted, { draggable: true })
  } else {
    customRow.value = createCustomRow(table, onSorted, { draggable: false })
  }
}, {immediate: true});
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
          <template v-if="column.key === 'name'">
            <div v-if="editableData[record.id]">
              <a-form
                :model="editableData[record.id]"
                :rules="genRules(record)"
                @finish="save(record.id)"
              >
                <a-form-item name="name" class="mb-0">
                  <div class="flex items-center justify-between gap-12">
                    <a-input
                      v-model:value="editableData[record.id].name"
                      :maxlength="50"
                      show-count
                    />
                    <a-button type="text" html-type="submit" size="small" class="!h-22 !p-0">
                      <CheckOutlined />
                    </a-button>
                  </div>
                </a-form-item>
              </a-form>
            </div>
            <div v-else class="flex items-center justify-between group">
              <div class="truncate">{{ record.name }}</div>
              <a-button type="text" size="small" class="group-hover:inline-block tw-hidden !h-22 !p-0">
                <EditOutlined @click="edit(record.id)"/>
              </a-button>
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
                    <div @click="updateTagGroup(record.id, {status: 1})">启用</div>
                  </a-menu-item>
                  <a-menu-item>
                    <div @click="updateTagGroup(record.id, {status: 0})">禁用</div>
                  </a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>
          </template>
          <template v-if="column.key === 'operation'">
            <div class="gap-16 flex">
              <div class="cursor-pointer text-[--primary-color]" @click="reviewTagGroup(record.id)">管理</div>
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
