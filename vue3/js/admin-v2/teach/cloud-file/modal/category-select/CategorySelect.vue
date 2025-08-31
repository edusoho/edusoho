<script setup>
import {ref} from 'vue';
import Api from '../../../../../../api';

const selectedId = ref();
const treeData = ref([]);

const emit = defineEmits(['setCategorySuccess'])
const modalVisible = defineModel();
const props = defineProps({
  ids: {
    type: Array,
    default: [],
  },
});

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
  treeData.value = transformCategory(res);
}
getCategories();

function closeModal() {
  modalVisible.value = false;
  selectedId.value = null;
}

async function confirm() {
  const params = {
    ids: props.ids,
    categoryId: selectedId.value,
  }
  await Api.uploadFile.setCategory(params);
  closeModal();
  emitter.emit('set-category-success');
  emit('setCategorySuccess')
}
</script>

<template>
  <a-modal
    v-model:open="modalVisible"
    centered
    title="设置分类"
    cancel-text="取消"
    ok-text="确定"
    :body-style="{ padding: '0' }"
    :ok-button-props="{ disabled: !selectedId }"
    :after-close="closeModal"
    @ok="confirm"
  >
    <div class="flex items-center gap-16 my-28 mx-36">
      <div class="text-16 font-normal leading-28 text-[rgba(0,0,0,0.88)]">分类</div>
      <a-tree-select
        class="grow"
        v-model:value="selectedId"
        :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
        placeholder="请选择分类"
        allow-clear
        tree-default-expand-all
        :tree-data="treeData"
        show-search
        tree-node-filter-prop="label"
      >
      </a-tree-select>
    </div>
  </a-modal>
</template>
