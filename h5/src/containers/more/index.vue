<template>
  <div class="more">
    <treeSelect></treeSelect>
    <lazyLoading></lazyLoading>
  </div>
</template>

<script>
  import treeSelect from '../components/e-tree-select/e-tree-select.vue';
  import lazyLoading from '../components/e-lazy-loading/e-lazy-loading.vue';

  export default {
    components: {
      treeSelect,
      lazyLoading,
    },
    data() {
      return {
      };
    },
  }
</script>
