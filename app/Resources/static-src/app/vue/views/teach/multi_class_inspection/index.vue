<template>
  <aside-layout :breadcrumbs="[{ name: '班课巡检' }]">
    <a-spin class="multi-class-inspection" :spinning="getListLoading">
      <div class="inspection-title">今日课程</div>
      <a-row :gutter="[24,24]">
        <a-col :sm="24" :lg="12" :xl="8" :xxl="6" v-for="inspection in inspectionList" :key="inspection.id">
          <inspection-card :inspection="inspection" />
        </a-col>
      </a-row>
      <empty v-if="!getListLoading && !inspectionList.length" />
    </a-spin>
  </aside-layout>
</template>

<script>
import AsideLayout from "app/vue/views/layouts/aside.vue";
import Empty from "app/vue/views/components/Empty.vue";
import InspectionCard from "./InspectionCard.vue";
import MultiClassInspection from "common/vue/service/MultiClassInspection";

export default {
  name: "index",
  components: {
    AsideLayout,
    Empty,
    InspectionCard,
  },

  data() {
    return {
      inspectionList: [],
      getListLoading: false,
    };
  },

  computed: {},

  created() {
    this.getMultiClassInspectionList();
  },

  methods: {
    async getMultiClassInspectionList() {
      this.getListLoading = true;
      try {
        this.inspectionList = await MultiClassInspection.search();
      } finally {
        this.getListLoading = false;
      }
    },
  },
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