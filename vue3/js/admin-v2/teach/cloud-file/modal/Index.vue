<script setup>
import {ref} from 'vue';
import CategorySelectModal from './category-select/CategorySelectModal.vue';
import AntConfigProvider from '../../../../components/AntConfigProvider.vue';

const params = ref();

const categoryModalVisible = ref(false);
window.emitter.on('open-category-modal', (val) => {
  if (val.ids.length > 0) {
    params.value = val;
    categoryModalVisible.value = true;
  }
});
</script>

<template>
  <AntConfigProvider>
    <CategorySelectModal
      v-model="categoryModalVisible"
      :params="params"
    />
  </AntConfigProvider>
</template>

