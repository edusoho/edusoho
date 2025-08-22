<script setup>
import {ref} from 'vue';

const modalVisible = ref(false);

emitter.on('open-category-modal', (params) => {
  if (params.ids.length > 0) {
    ids.value = params.ids;
    modalVisible.value = true;
  }
});
</script>

<template>

</template>

<style scoped lang="less">

</style>
