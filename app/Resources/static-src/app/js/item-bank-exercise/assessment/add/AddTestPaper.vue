<script>
export default {
  name: 'AddTestPaper',
  methods: {
    showDrawer() {
      document.getElementById('add-test-paper-drawer-visible').value = true;
    }
  },
};
</script>

<template>
  <a-button @click="showDrawer">{{ 'item_bank_exercise.addTestpaper'|trans }}</a-button>
</template>
