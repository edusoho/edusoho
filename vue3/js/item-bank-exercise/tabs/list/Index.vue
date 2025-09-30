<script setup>
import {ref, onMounted} from 'vue';
import ListSection from './ListSection.vue';
import AntConfigProvider from '../../../components/AntConfigProvider.vue';
import {t} from './vue-lang';

const props = defineProps({
  categoryTree: {type: Array, default: []},
  records: {type: Object, default: {}},
  previewAs: {type: String, default: null},
  member: {type: Object, default: {}},
  exercise: {type: Object, default: {}},
  moduleId: {type: Number, default: null},
});

const newCategoryTree = ref([]);
const selectedChapterId = ref(null);

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

function selectChapter(chapterId) {
  selectedChapterId.value = chapterId;
}

onMounted(() => {
  newCategoryTree.value = nestItems(props.categoryTree);
});
</script>

<template>
  <ant-config-provider>
    <div v-if="newCategoryTree.length > 0" class="w-full mt-8">
      <div v-for="(item, index) in newCategoryTree" :key="item.id" :ref="item.id">
        <list-section
          :chapter="item"
          :records="props.records"
          :member="props.member"
          :preview-as="props.previewAs"
          :is-last="index + 1 === newCategoryTree.length"
          :exercise="props.exercise"
          :module-id="props.moduleId"
          :selected-chapter-id="selectedChapterId"
          @select-chapter="selectChapter"
        >
        </list-section>
      </div>
    </div>
    <a-empty v-else class="mt-50" :description="t('empty')"/>
  </ant-config-provider>
</template>
