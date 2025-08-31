<script setup>
import {emitter} from 'vue3/js/event-bus';
import CategorySelect from './CategorySelect.vue';
import ReferencedCourse from './ReferencedCourse.vue';
import {ref} from 'vue';

const params = ref({});

const categorySelectModalVisible = ref(false);
emitter.on('open-category-modal', (params) => {
  if (params.ids.length > 0) {
    params.value = params
    categorySelectModalVisible.value = true;
  }
});

const referencedCourseModalVisible = ref(false);
emitter.on('open-refer-course-modal', (params) => {
  if (Number(params.referCourse) > 0) {
    params.value = params;
    referencedCourseModalVisible.value = true;
  }
});

function clearParams() {
  params.value = {};
}
</script>

<template>
  <CategorySelect
    v-model="categorySelectModalVisible"
    :ids="params.ids"
    @set-category-success="clearParams"
  />
  <ReferencedCourse
    v-model="referencedCourseModalVisible"
    :id="params.id"
  />
</template>

