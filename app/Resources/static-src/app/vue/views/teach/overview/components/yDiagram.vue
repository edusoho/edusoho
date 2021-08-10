<template>
  <div id="yDiagram"></div>
</template>

<script>
import { Chart } from "@antv/g2";

export default {
  name: "yDiagram",
  components: {},

  data() {
    return {
      chartDataDouble: [
        { item: "事例一", count: 40, percent: 0.4 },
        { item: "事例二", count: 21, percent: 0.21 },
      ],
    };
  },

  computed: {},

  mounted() {
    this.initChart();
  },

  methods: {
    initChart() {
      const chart = new Chart({
        container: "yDiagram",
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
        .label("count", (percent) => {
          return {
            content: (data) => {
              return data.item;
            },
          };
        })
        .tooltip("item*count", (item, count) => {
          return {
            name: item,
            value: count,
          };
        });

      chart.interaction("element-active");

      chart.render();
    },
  },
};
</script>
<style  scoped>
</style>