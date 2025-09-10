<script setup>
import AntConfigProvider from '../../../components/AntConfigProvider.vue';
import TagSelectModal from '../../widgets/TagSelectModal.vue';
import {ref} from 'vue';

const params = ref({})

const tagSelectModalVisible = ref(false);
window.emitter.on('open-tag-modal', (val) => {
  tagSelectModalVisible.value = true;
  params.value = val;
});
</script>

<template>
  <AntConfigProvider>
    <TagSelectModal
      v-model="tagSelectModalVisible"
      :params="params"
    />
  </AntConfigProvider>
</template>
