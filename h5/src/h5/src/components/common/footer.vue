<template>
  <div class="ibs-footer">
    <div
      v-for="(item, index) in footerItem"
      :key="index"
      @click="check(item.type)"
      :class="[getActive(item.type)]"
    >
      <i :class="['wap-icon', item.iconName]"></i>
      {{ item.name }}
    </div>
  </div>
</template>
<script>
const doFooter = [
  {
    name: "题卡",
    iconName: "wap-icon-Questioncard",
    type: "card"
  },
  {
    name: "保存进度",
    iconName: "wap-icon-jindu",
    type: "process"
  },
  {
    name: "提交",
    iconName: "wap-icon-submit",
    type: "submit"
  }
];
const noProcessDoFooter = [
  {
    name: "题卡",
    iconName: "wap-icon-Questioncard",
    type: "card"
  },
  {
    name: "提交",
    iconName: "wap-icon-submit",
    type: "submit"
  }
];
const report = [
  {
    name: "题卡",
    iconName: "wap-icon-Questioncard",
    type: "card"
  },
  {
    name: "错题",
    iconName: "wap-icon-cuoti1",
    activeIconName: "wap-icon-cuoti1-active",
    type: "wrong"
  }
];
const review = [
  {
    name: "题卡",
    iconName: "wap-icon-Questioncard",
    type: "card"
  },
  {
    name: "提交",
    iconName: "wap-icon-tijiao",
    type: "review"
  }
];
export default {
  name: "ibs-footer",
  data() {
    return {};
  },
  computed: {
    footerItem() {
      if (this.mode === "do") {
        return this.showSaveProcessBtn ? doFooter : noProcessDoFooter;
      } else if (this.mode === "review") {
        return review;
      }
      return report;
    }
  },
  props: {
    // 模式 report:答题结果模式 do:做题模式
    mode: {
      type: String,
      default: "do"
    },
    wrongMode: {
      type: Boolean,
      default: false
    },

    showSaveProcessBtn: {
      type: Boolean,
      default: true
    }
  },
  methods: {
    check(type) {
      switch (type) {
        case "card":
          this.$emit("showcard");
          break;
        case "process":
          this.$emit("submitPaper", false);
          break;
        case "submit":
          this.$emit("submitPaper", true);
          break;
        case "wrong":
          this.$emit("lookWrong");
          break;
        case "analysis":
          this.$emit("lookAnalysis");
          break;
        case "review":
          this.$emit("submitReview");
          break;
      }
    },
    getActive(type) {
      if (this.wrongMode && type === "wrong") {
        return "wap-icon-cuoti1-active";
      }
    }
  }
};
</script>
