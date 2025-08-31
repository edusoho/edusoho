<script setup>
import {emitter} from 'vue3/js/event-bus';
import CategorySelect from './category-select/CategorySelect.vue';
import ReferCourse from './refer-course/ReferCourse.vue';
import {ref} from 'vue';
import AntConfigProvider from '../../../../components/AntConfigProvider.vue';

const params = ref({});

const categorySelectModalVisible = ref(false);
emitter.on('open-category-modal', (val) => {
  if (val.ids.length > 0) {
    params.value = val
    categorySelectModalVisible.value = true;
  }
});

const referCourseModalVisible = ref(false);
emitter.on('open-refer-course-modal', (val) => {
  if (Number(val.referCourse) > 0) {
    params.value = val;
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

