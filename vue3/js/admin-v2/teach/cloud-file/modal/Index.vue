<script setup>
import {emitter} from 'vue3/js/event-bus';
import CategorySelect from './category-select/CategorySelect.vue';
import ReferCourse from './refer-course/ReferCourse.vue';
import {ref} from 'vue';
import AntConfigProvider from '../../../../components/AntConfigProvider.vue';

const params = ref({});

const categorySelectModalVisible = ref(false);
emitter.on('open-category-modal', (params) => {
  if (params.ids.length > 0) {
    params.value = params
    categorySelectModalVisible.value = true;
  }
});

const referCourseModalVisible = ref(false);
emitter.on('open-refer-course-modal', (params) => {
  if (Number(params.referCourse) > 0) {
    params.value = params;
    referCourseModalVisible.value = true;
  }
});

function clearParams() {
  params.value = {};
}
</script>

<template>
  <AntConfigProvider>
    <CategorySelect
      v-model="categorySelectModalVisible"
      :ids="params.ids"
      @set-category-success="clearParams"
    />
    <ReferCourse
      v-model="referCourseModalVisible"
      :id="params.id"
    />
  </AntConfigProvider>
</template>

