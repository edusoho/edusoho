<script setup>
import {reactive, ref, watch} from 'vue';
import Api from '../../../../../../api';
import {formatDate} from '../../../../../common';

const emit = defineEmits(['cancel', 'ok'])
const modalVisible = defineModel();
const props = defineProps({
  courseSetIds: {
    type: Array,
    default: [],
  },
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
  }
});

watch(() => modalVisible.value, (newValue) => {
  if (newValue) {
    getCategories();
    getTagOptions();
    formState.fileId = props.fileId;
    isEditFile.value = false;
  }
})

const formRef = ref();
const formState = reactive({
  fileId: '',
});
const rules = reactive({
  fileId: [
    { required: true, message: '请选择文件', trigger: 'blur' }
  ],
});

const sourceFrom = ref('my')
const sourceOptions = [
  {
    label: '来自上传',
    value: 'my',
  },
  {
    label: '来自分享',
    value: 'sharing',
  },
  {
    label: '公共资源',
    value: 'public',
  },
]

const categoryId = ref();
const categoryTreeData = ref([]);
function transformCategory(res) {
  return res.map(item => {
    const node = {
      label: item.name,
      value: item.id,
    }
    if (item.children && item.children.length > 0) {
      node.children = transformCategory(item.children)
    }
    return node
  })
}
async function getCategories() {
  const res = await Api.category.getCategories('course')
  categoryTreeData.value = transformCategory(res);
}

const tagId = ref();
const tagOptions = ref([]);
async function getTagOptions() {
  const res = await Api.tag.getTag();
  tagOptions.value = res.map(item => ({
    value: item.id,
    label: item.name,
  }));
}

watch([sourceFrom, categoryId, tagId], () => {
  onSearch();
})

const filename = ref();

const filterOption = (input, option) => {
  return option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0;
};

const fileTypeText = (type) => {
  switch (type) {
    case 'video':
      return '视频'
    case 'audio':
      return '音频'
    case 'image':
      return '图片'
    case 'document':
      return '文档'
    case 'ppt':
      return 'PPT'
    case 'other':
      return '其他'
    default:
      return ''
  }
}

function onReset() {
  formState.fileId = '';
  sourceFrom.value = 'my';
  categoryId.value = null;
  categoryTreeData.value = [];
  tagId.value = null;
  tagOptions.value = [];
  filename.value = null;
  selectedFile.value = null;
}

function closeReplaceUploadFileModal() {
  onReset();
  modalVisible.value = false;
}

function onCancel() {
  formRef.value.clearValidate();
  closeReplaceUploadFileModal();
  emit('cancel')
}

async function onOk() {
  formRef.value.validate()
    .then(async () => {
      if (formState.fileId !== props.fileId) {
        const params = {
          fileId: formState.fileId,
          courseSetIds: props.courseSetIds
        }
        await Api.file.replaceUploadFile(props.fileId, params)
      }
      emit('ok')
      closeReplaceUploadFileModal();
      window.emitter.emit('replace-upload-file-success');
    })
}

const isEditFile = ref(false);
function onEdit() {
  onSearch()
  formState.fileId = null;
  selectedFile.value = null;
  isEditFile.value = true;
}

const spinning = ref(false);
const uploadFiles = ref([])
async function onSearch() {
  spinning.value = true;
  const params = {
    type: props.fileType,
    sourceFrom: sourceFrom.value,
    tagId: tagId.value,
    categoryId: categoryId.value,
    filename: filename.value,
    offset: 0,
    limit: 300,
  }
  const res = await Api.file.searchUploadFile(params)
  uploadFiles.value = res.data
  spinning.value = false;
}

const selectedFile = ref(null)
function onSelect(record) {
  selectedFile.value = record;
  formState.fileId = record.id;
  uploadFiles.value = [];
  isEditFile.value = false;
  formRef.value.validate();
}
</script>

<template>
  <a-modal v-model:open="modalVisible"
           :width="674"
           :keyboard="false"
           :maskClosable="false"
           centered
           :focus-lock="false"
           @cancel="onCancel"
           @ok="onOk"
  >
    <template #title>
      <div class="flex items-center gap-12">
        <div class="text-[rgba(0,0,0,0.88)] text-[16px] font-medium leading-[24px]">资源替换</div>
        <div class="text-[rgba(0,0,0,0.88)] text-[14px] font-normal">{{ `已选：${courseSetIds.length}` }}</div>
      </div>
    </template>
    <a-form
      class="my-24 mx-36"
      ref="formRef"
      :model="formState"
      :rules="rules"
      :label-col="{span: 3}"
      autocomplete="off"
    >
      <a-form-item
        :label="fileTypeText(fileType)"
        name="fileId"
      >
        <a-form-item-rest>
          <div v-if="!isEditFile" class="flex items-center gap-12">
            <div class="text-[rgba(0,0,0,0.45)] text-[14px] font-normal leading-[22px]">{{ selectedFile ? selectedFile.filename : props.filename }}</div>
            <div class="flex items-center gap-4 cursor-pointer">
              <img src="../../../../../../img/admin-v2/teach/cloud-file/modal/refer-course/edit.svg" alt="">
              <div class="text-[rgba(0,0,0,0.88)] text-[12px] font-normal" @click="onEdit">编辑</div>
            </div>
          </div>
          <div v-else>
            <div class="border border-[#F0F0F0] rounded-[8px] border-solid flex flex-col">
              <div class="flex flex-col border-b border-x-0 border-t-0 border-[#F0F0F0] border-solid p-8 gap-12">
                <div class="flex justify-between gap-12">
                  <a-select
                    class="w-147 max-w-147"
                    v-model:value="sourceFrom"
                    :options="sourceOptions"
                  />
                  <a-tree-select
                    class="w-147 max-w-147"
                    v-model:value="categoryId"
                    placeholder="选择分类"
                    tree-default-expand-all
                    allow-clear
                    :tree-data="categoryTreeData"
                    show-search
                    tree-node-filter-prop="label"
                  />
                  <a-select
                    class="w-147 max-w-147"
                    v-model:value="tagId"
                    show-search
                    allow-clear
                    placeholder="选择标签"
                    :options="tagOptions"
                    :filter-option="filterOption"
                  ></a-select>
                </div>
                <div class="flex gap-12">
                  <a-input
                    class="w-360"
                    allow-clear
                    v-model:value="filename"
                    :placeholder="`请输入${fileTypeText(fileType)}关键字`"
                  />
                  <a-button type="primary" ghost @click="onSearch">搜索</a-button>
                </div>
              </div>
              <a-spin :spinning="spinning">
                <div v-if="uploadFiles.length > 0" class="flex flex-col max-h-120 overflow-y-auto gap-12 p-12">
                  <div v-for="(record, index) in uploadFiles" :key="record.id" class="flex justify-between text-[rgba(0,0,0,0.65)] text-[14px] font-normal leading-[16px] cursor-pointer" @click="onSelect(record)">
                    <div class="max-w-250 truncate">{{ record.filename }}</div>
                    <div class="flex gap-12">
                      <div>{{ record.fileSize }}</div>
                      <div class="w-88 text-right">{{ formatDate(record.createdTime, 'YYYY-MM-DD') }}</div>
                    </div>
                  </div>
                </div>
                <div v-else class="text-[rgba(0,0,0,0.45)] text-[14px] font-normal leading-[16px] text-center p-12">没有资源</div>
              </a-spin>
            </div>
          </div>
        </a-form-item-rest>
      </a-form-item>
      <a-form-item
        v-if="['video', 'audio'].includes(fileType) && !isEditFile"
        :label="`${fileTypeText(fileType)}时长`"
      >
        <div class="text-[rgba(0,0,0,0.45)] text-[14px] font-normal leading-[22px]">{{ `${Math.floor((selectedFile?.length ? selectedFile.length : fileLength) / 60)} 分 ${(selectedFile?.length ? selectedFile.length : fileLength) % 60 !== 0 ? (selectedFile?.length ? selectedFile.length : fileLength) % 60 : '00'} 秒` }}</div>
      </a-form-item>
    </a-form>
  </a-modal>
</template>

