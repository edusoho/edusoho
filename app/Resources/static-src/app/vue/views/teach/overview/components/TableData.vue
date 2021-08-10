<template>
  <div class="table-data">
    <div class="title">
      <span class="text">{{title}}</span>
      <a-radio-group class="title-button" default-value="up" button-style="solid" @change="changeOrder">
        <a-radio-button value="up">
          升序
        </a-radio-button>
        <a-radio-button value="down">
          降序
        </a-radio-button>
      </a-radio-group>
    </div>
    <a-table :columns="columns" :data-source="data" :pagination="false">
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
      type: Array,
      required: true,
      default: [],
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
      },
      {
        title: "班级名称",
        dataIndex: "class",
        ellipsis: true,
      },
      {
        title: "昨日新增人数",
        dataIndex: "add",
        ellipsis: true,
      },
    ];
    return {
      columns,
    };
  },

  computed: {},

  mounted() {},

  methods: {
    changeOrder(res) {
      console.log(res.target.value);
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