<template>
  <aside-layout :breadcrumbs="[{ name: '教务仪表盘' }]">
    <a-row :gutter="24">
      <a-col :span="6">
        <a-card>
          <p>card content</p>
        </a-card>
      </a-col>
      <a-col :span="6">
        <a-card>
          <p>card content</p>
        </a-card>
      </a-col>
      <a-col :span="6">
        <a-card>
          <p>card content</p>
        </a-card>
      </a-col>
      <a-col :span="6">
        <router-link :to="{ name: 'Overtime' }">
          <a-card>
            <p>card content</p>
          </a-card>
        </router-link>

      </a-col>
    </a-row>
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