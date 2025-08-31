<script setup>
const modalVisible = defineModel();
const props = defineProps({
  ids: {
    type: Array,
    default: [],
  },
});
</script>

<template>
  <a-modal v-model:open="modalVisible"
           title="引用详情"
           :width="674"
           centered
  >
    <div>1111111111</div>
  </a-modal>
</template>

