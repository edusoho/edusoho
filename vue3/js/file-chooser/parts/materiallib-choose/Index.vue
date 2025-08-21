<script setup>
import { ref, watch } from 'vue';
import Api from '../../../../api';
import AntConfigProvider from '../../../components/AntConfigProvider.vue';
const selectedCategoryId = ref();
const categoryTreeData = ref([]);

watch(selectedCategoryId, () => {
  const categoryIdInput = document.querySelector('input[name="categoryId"]');
  categoryIdInput.value = selectedCategoryId.value ? selectedCategoryId.value : '';
  categoryIdInput.dispatchEvent(new Event('change', { bubbles: true }));
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
  categoryTreeData.value = transformCategory(res);
}
getCategories();
</script>

<template>
  <AntConfigProvider>
    <a-tree-select
      v-model:value="selectedCategoryId"
      :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
      placeholder="--选择分类--"
      allow-clear
      tree-default-expand-all
      :tree-data="categoryTreeData"
      show-search
      tree-node-filter-prop="label"
      style="width: 200px"
      placement="bottomRight"
      :get-popup-container="triggerNode => triggerNode.parentNode"
    >
    </a-tree-select>
  </AntConfigProvider>
</template>
