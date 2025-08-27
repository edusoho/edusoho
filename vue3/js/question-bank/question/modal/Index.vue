<script setup>
import AntConfigProvider from '../../../components/AntConfigProvider.vue';
import {emitter} from 'vue3/js/event-bus';
import TagSelect from '../../../admin-v2/teach/cloud-file/modal/tagSelect.vue';
import {ref} from 'vue';

const id = ref()
const ids = ref([])

const tagSelectModalVisible = ref(false);
emitter.on('open-tag-modal', (params) => {
  tagSelectModalVisible.value = true;
  id.value = params.id;
});
</script>

<template>
  <AntConfigProvider>
    <TagSelect
      v-model="tagSelectModalVisible"
      :id="id"
    />
  </AntConfigProvider>
</template>
