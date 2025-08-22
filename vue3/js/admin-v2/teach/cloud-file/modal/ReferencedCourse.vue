<script setup>
// const emit = defineEmits(['setCategorySuccess'])
const modalVisible = defineModel();
const props = defineProps({
  id: {
    type: String,
    default: '',
  },
});
</script>

<template>
  <a-modal v-model:open="modalVisible"
           title="Basic Modal"
           centered
  >
    <p>Some contents...</p>
    <p>Some contents...</p>
    <p>Some contents...</p>
  </a-modal>
</template>
