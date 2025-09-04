<template>
  <AntConfigProvider>
    <div class="flex ml-10 items-center">
      <div class="text-[14px] text-[rgba(51,51,51,0.88)] text-nowrap mr-8">分类:</div>
      <a-tree-select
        v-model:value="selectedCategoryId"
        :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
        placeholder="请选择"
        allow-clear
        tree-default-expand-all
        :tree-data="categoryTreeData"
        show-search
        tree-node-filter-prop="label"
        style="width: 172px"
      >
      </a-tree-select>
    </div>
  </AntConfigProvider>
</template>

<script setup>
import { ref, watch } from 'vue';
import Api from '../../../../../api';
import AntConfigProvider from '../../../../components/AntConfigProvider.vue';
const selectedCategoryId = ref();
const categoryTreeData = ref([]);

watch(selectedCategoryId, () => {
  const categoryIdInput = document.querySelector('input[name="categoryId"]');
  categoryIdInput.value = selectedCategoryId.value ? selectedCategoryId.value : '';
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
