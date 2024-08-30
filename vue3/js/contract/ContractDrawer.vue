<script setup>

const model = defineModel('visible');
const onClose = () => {
  model.value = false;
};
</script>

<template>
  <a-drawer
    v-model:open="model"
    width="60vw"
    :closable="false"
    :footer-style="{ textAlign: 'right' }"
    @close="onClose"
    :rootStyle="{'z-index': 1050}"
  >
    <template #footer>
      <a-button style="margin-right: 8px" @click="onClose">取消</a-button>
      <a-button type="primary" @click="onClose">确定</a-button>
    </template>
  </a-drawer>
</template>

<style scoped>
</style>
