<script setup>
import AntConfigProvider from '../../../components/AntConfigProvider.vue';
import {emitter} from 'vue3/js/event-bus';
import TagSelect from '../../components/tagSelect.vue';
import {ref} from 'vue';

const params = ref({})

const tagSelectModalVisible = ref(false);
emitter.on('open-tag-modal', (val) => {
  tagSelectModalVisible.value = true;
  params.value = val;
});
</script>

<template>
  <AntConfigProvider>
    <TagSelect
      v-model="tagSelectModalVisible"
      :params="params"
    />
  </AntConfigProvider>
</template>
