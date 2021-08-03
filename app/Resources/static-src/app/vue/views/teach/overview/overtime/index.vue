<template>
  <aside-layout :breadcrumbs="[{ name: '超时未批阅作业' }]">

  </aside-layout>
</template>

<script>
import AsideLayout from "app/vue/views/layouts/aside.vue";
import { Card } from "ant-design-vue";

export default {
  name: "index",
  components: {
    AsideLayout,
    ACard: Card,
  },

  data() {
    return {};
  },

  computed: {},

  mounted() {},

  methods: {},
};
</script>
<style  scoped>
</style>