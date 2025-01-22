<script setup>
import {ref, onMounted} from 'vue';
import ChapterListSection from './ChapterListSection.vue';
import AntConfigProvider from '../../../components/AntConfigProvider.vue';

const props = defineProps({
  categoryTree: {type: Array, default: []},
  records: {type: Object, default: {}},
  previewAs: {type: String, default: null},
  member: {type: Object, default: {}},
  exercise: {type: Object, default: {}},
  moduleId: {type: Number, default: null},
});

const newCategoryTree = ref([]);

function nestItems(data) {
  const idMap = {};
  data.forEach(item => {
    idMap[item.id] = { ...item, children: [] };
  });
  const result = [];
  data.forEach(item => {
    const parentId = item.parent_id;
    if (parentId === "0") {
      result.push(idMap[item.id]);
    } else {
      const parent = idMap[parentId];
      if (parent) {
        parent.children.push(idMap[item.id]);
      }
    }
  });
  return result;
}

onMounted(() => {
  newCategoryTree.value = nestItems(props.categoryTree);
});
</script>

<template>
  <ant-config-provider>
    <div v-if="newCategoryTree.length > 0" class="w-full mt-8">
      <div v-for="(item, index) in newCategoryTree" :key="item.id" :ref="item.id">
        <chapter-list-section
          :chapter="item"
          :records="props.records"
          :member="props.member"
          :preview-as="props.previewAs"
          :is-last="index + 1 === newCategoryTree.length"
          :exercise="props.exercise"
          :module-id="props.moduleId"
        >
        </chapter-list-section>
      </div>
    </div>
    <a-empty v-else class="mt-50" description="暂无题目"/>
  </ant-config-provider>
</template>
