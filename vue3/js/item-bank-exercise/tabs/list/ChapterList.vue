<script setup>
const props = defineProps({
  categoryTree: {type: Array, default: []}
});
</script>

<template>
  <div>{{ props.categoryTree }}</div>
</template>

