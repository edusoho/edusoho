<script setup>
import {emitter} from 'vue3/js/event-bus';
import CategorySelect from './CategorySelect.vue';
import ReferencedCourse from './ReferencedCourse.vue';
import {ref} from 'vue';

const id = ref()
const ids = ref([])

const categorySelectModalVisible = ref(false);
emitter.on('open-category-modal', (params) => {
  if (params.ids.length > 0) {
    ids.value = params.ids;
    categorySelectModalVisible.value = true;
  }
});

const referencedCourseModalVisible = ref(false);
emitter.on('open-referenced-course-modal', (params) => {
  if (Number(params.referencedCourse) > 0) {
    id.value = params.id;
    referencedCourseModalVisible.value = true;
  }
});

function clearIds() {
  ids.value = [];
}
function clearId() {
  id.value = null;
}
</script>

<template>
  <CategorySelect
    v-model="categorySelectModalVisible"
    :ids="ids"
    @set-category-success="clearIds"
  />
  <ReferencedCourse
    v-model="referencedCourseModalVisible"
    :id="id"
  />
</template>

