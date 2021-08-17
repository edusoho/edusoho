<template>
  <div class="table-data">
    <div class="title">
      <span class="text">{{ title }}</span>
      <a-radio-group class="title-button" default-value="descSort" button-style="solid" @change="changeOrder">
        <a-radio-button value="ascSort">
          升序
        </a-radio-button>
        <a-radio-button value="descSort">
          降序
        </a-radio-button>
      </a-radio-group>
    </div>
    <a-table :columns="columns" :data-source="tableData" :pagination="false" row-key="courseId">
      <template slot="rank" slot-scope="text, record, index">
        {{ index + 1 }}
      </template>
      <template slot="rate" slot-scope="rate, record">
        <span v-if="rate || rate === 0">{{ rate * 100 }}%</span>
        <span v-else>{{ record.count }}</span>
      </template>
      <template slot="rateTitle">
        <span>{{ title }}</span>
      </template>
    </a-table>
  </div>
</template>

<script>
export default {
  name: "TableData",
  components: {},
  props: {
    title: {
      type: String,
      require: true,
    },
    data: {
      type: Object,
      required: true,
    },
  },

  data() {
    const columns = [
      {
        title: "排名",
        dataIndex: "rank",
        align: "center",
        width: "30%",
        ellipsis: true,
        scopedSlots: { customRender: "rank" },
      },
      {
        title: "班级名称",
        dataIndex: "multiClass",
        ellipsis: true,
      },
      {
        dataIndex: "rate",
        ellipsis: true,
        scopedSlots: { customRender: "rate", title: "rateTitle" },
      },
    ];
    return {
      columns,
      order: "descSort",
    };
  },

  computed: {
    tableData() {
      const { ascSort, descSort } = this.data;
      return this.order === "ascSort" ? ascSort : descSort;
    },
  },

  mounted() {},

  methods: {
    changeOrder(res) {
      this.order = res.target.value;
    },
  },
};
</script>
<style lang="less"  scoped>
.title {
  position: relative;
  height: 52px;
  line-height: 36px;
  margin-top: 36px;
  .text {
    font-size: 16px;
    color: #333333;
    font-weight: 500;
  }
  .title-button {
    position: absolute;
    right: 0;
  }
}
</style>