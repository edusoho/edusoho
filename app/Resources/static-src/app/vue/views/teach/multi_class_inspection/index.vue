<template>
  <aside-layout :breadcrumbs="[{ name: '班课巡检' }]">
    <a-spin class="multi-class-inspection" :spinning="getListLoading || ajaxProductLoading">
      <div class="inspection-title">今日课程</div>
      <a-row :gutter="[24,24]">
        <a-col :sm="24" :lg="12" :xl="6">
          <inspection-card />
        </a-col>
      </a-row>
      <empty v-if="!(getListLoading || ajaxProductLoading) && !productList.length" />

    </a-spin>

  </aside-layout>
</template>

<script>
import AsideLayout from "app/vue/views/layouts/aside.vue";
import Empty from "app/vue/views/components/Empty.vue";
import InspectionCard from "./InspectionCard.vue";

export default {
  name: "index",
  components: {
    AsideLayout,
    Empty,
    InspectionCard,
  },

  data() {
    return {
      productList: [],
      getListLoading: false,
      ajaxProductLoading: false,
    };
  },

  computed: {},

  mounted() {},

  methods: {},
};
</script>
<style lang='less' scoped>
.multi-class-inspection {
  .inspection-title {
    font-size: 16px;
    color: #333333;
    letter-spacing: 0;
    line-height: 24px;
    font-weight: 400;
  }
}
</style>