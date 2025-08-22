<script setup>
import {emitter} from 'vue3/js/event-bus';
import CategorySelect from './CategorySelect.vue';
import {ref} from 'vue';

const ids = ref([])

const categorySelectModalVisible = ref(false);
emitter.on('open-category-modal', (params) => {
  if (params.ids.length > 0) {
    ids.value = params.ids;
    categorySelectModalVisible.value = true;
  }
});

function clearIds() {
  ids.value = [];
}
</script>

<template>
  <CategorySelect
    v-model="categorySelectModalVisible"
    :ids="ids"
    @set-category-success="clearIds"
  />
</template>

