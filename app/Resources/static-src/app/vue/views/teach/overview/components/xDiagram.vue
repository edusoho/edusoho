<template>
  <div>
    <div id="xDiagram"></div>
  </div>
</template>

<script>
import { Chart } from "@antv/g2";

export default {
  name: "xDiagram",
  components: {},
  props: {
    graphicData: Object,
  },
  data() {
    return {};
  },

  computed: {
    chartDataDouble() {
      return [
        { item: "已开班班课", count: this.graphicData.startNum },
        { item: "未开班班课", count: this.graphicData.notStartNum },
      ];
    },
  },

  mounted() {},

  watch: {
    chartDataDouble: {
      handler() {
        this.initChart();
      },
      deep: true,
    },
  },

  methods: {
    initChart() {
      const chart = new Chart({
        container: "xDiagram",
        autoFit: true,
        height: 258,
      });
      chart.data(this.chartDataDouble);

      chart.coordinate("theta", {
        radius: 0.7,
        innerRadius: 0.6,
      });
      chart.legend({
        position: "left",
        background: { padding: [0, 0, 0, 60], style: "border:unset" },
        itemHeight: 80,
        itemName: {
          formatter: (text, item, index) => {
            return `${text}: ${this.chartDataDouble[index].count}`;
          },
          style: {
            fontSize: 18,
          },
        },
      });
      chart.tooltip({
        showTitle: false,
        showMarkers: false,
        itemTpl:
          '<li class="g2-tooltip-list-item"><span style="background-color:{color};" class="g2-tooltip-marker"></span>{name}: {value}</li>',
      });

      chart
        .interval()
        .adjust("stack")
        .position("count")
        .color("item", ["#5AD8A6", "#5B8FF9"])
        .label("item", () => {
          return {
            offset: 18,
            style: {
              fontSize: 18,
            },
          };
        })
        .tooltip("item*count", (item, count) => {
          return {
            name: item,
            value: count,
          };
        });
      // 移除图例点击过滤交互
      chart.removeInteraction("legend-filter");

      chart.render();
    },
  },
};
</script>
<style  scoped>
</style>