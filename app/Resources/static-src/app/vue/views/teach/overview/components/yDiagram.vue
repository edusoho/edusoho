<template>
  <div id="yDiagram"></div>
</template>

<script>
import { Chart } from "@antv/g2";

export default {
  name: "yDiagram",
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
        { item: "在读学员人数", count: this.graphicData.studyNum },
        { item: "未开班学员人数", count: this.graphicData.notStudyNum },
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
        container: "yDiagram",
        autoFit: true,
        height: 258,
        defaultInteractions: ["tooltip"], // 仅保留 tooltip
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