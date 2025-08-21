<script setup>
import {emitter} from 'vue3/js/event-bus';
import {ref} from 'vue';
import Api from '../../../../../api';

const categoryModalVisible = ref(false);
const ids = ref([])
const selectedCategoryId = ref();
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
getCategories();

emitter.on('open-category-modal', (params) => {
  if (params.ids.length > 0) {
    ids.value = params.ids;
    categoryModalVisible.value = true;
  }
});

function closeCategoryModal() {
  categoryModalVisible.value = false;
  selectedCategoryId.value = null;
}

async function confirmCategory() {
  const params = {
    ids: ids.value,
    categoryId: selectedCategoryId.value,
  }
  await Api.uploadFile.setCategory(params);
  closeCategoryModal();
  emitter.emit('set-category-success');
}
</script>

<template>
  <a-modal
    v-model:open="categoryModalVisible"
    centered
    title="设置分类"
    cancel-text="取消"
    ok-text="确定"
    :body-style="{ padding: '0' }"
    :ok-button-props="{ disabled: !selectedCategoryId }"
    :after-close="closeCategoryModal"
    @ok="confirmCategory"
  >
    <div class="flex items-center gap-16 my-28 mx-36">
      <div class="text-16 font-normal leading-28 text-[rgba(0,0,0,0.88)]">分类</div>
      <a-tree-select
        class="grow"
        v-model:value="selectedCategoryId"
        :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
        placeholder="请选择分类"
        allow-clear
        tree-default-expand-all
        :tree-data="categoryTreeData"
        show-search
        tree-node-filter-prop="label"
      >
      </a-tree-select>
    </div>
  </a-modal>
</template>

