<template>
  <aside-layout :breadcrumbs="[{ name: '班课巡检' }]" :headerTip="headerTip">
    <a-spin class="multi-class-inspection" :spinning="getListLoading">
      <a-row :gutter="[24,24]">
        <a-col :sm="24" :lg="12" :xl="8" :xxl="6" v-for="(inspection, index) in inspectionList" :key="inspection.id">
          <inspection-card :inspection="inspection" :liveInfo="liveInfo[index]" />
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
      headerTip: "班课巡检仅展示今天所有直播课",
      liveInfo: [],
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

        let result = _.map(this.inspectionList, (inspection) => {
          return MultiClassInspection.getLiveInfoById({
            query: {
              id: inspection.activityId,
            },
          });
        });

        this.liveInfo = await Promise.all(result);
      } finally {
        this.getListLoading = false;
      }
    },
  },
};
</script>
<style lang='less' scoped>
</style>