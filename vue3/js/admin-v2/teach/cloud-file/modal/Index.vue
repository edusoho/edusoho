<script setup>
import CategorySelectModal from './category-select/CategorySelectModal.vue';
import ReferCourseModal from './refer-course/ReferCourseModal.vue';
import {ref} from 'vue';
import AntConfigProvider from '../../../../components/AntConfigProvider.vue';

const params = ref({});

const categorySelectModalVisible = ref(false);
window.emitter.on('open-category-modal', (val) => {
  if (val.ids.length > 0) {
    params.value = val
    categorySelectModalVisible.value = true;
  }
});

const referCourseModalVisible = ref(false);
window.emitter.on('open-refer-course-modal', (val) => {
  if (Number(val.usedCount) > 0) {
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
    <ReferCourseModal
      v-model="referCourseModalVisible"
      :params="params"
    />
    <CategorySelectModal
      v-model="categorySelectModalVisible"
      :params="params"
    />
  </AntConfigProvider>
</template>

