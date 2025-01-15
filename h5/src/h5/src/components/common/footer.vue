<template>
  <div class="fixed z-10 bottom-0 left-0 text-[#333] leading-32 drop-shadow-lg flex w-full bg-white" style="box-shadow: 0 -2px 4px 0 rgba(0, 0, 0, 0.1);">
    <div
      v-for="(item, index) in footerItem"
      :key="index"
      @click="check(item.type)"
      class="flex-1 flex flex-col items-center"
    >
      <div class="h-fit mt-8 mb-4" v-html="item.iconName"></div>
      <span class="text-12 font-normal mb-8" style="color: #5E6166">{{ item.name }}</span>
    </div>
  </div>
</template>
<script>
const icon = {
  QuestionCard: `<svg xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 25 24" fill="none">
                  <path d="M20 3H5C4.17157 3 3.5 3.67157 3.5 4.5V19.5C3.5 20.3284 4.17157 21 5 21H20C20.8284 21 21.5 20.3284 21.5 19.5V4.5C21.5 3.67157 20.8284 3 20 3Z" stroke="#37393D" stroke-width="1.5" stroke-linejoin="round"/>
                  <path d="M11 6.5H7V10.5H11V6.5Z" stroke="#37393D" stroke-width="1.5" stroke-linejoin="round"/>
                  <path d="M18 6.5H14V10.5H18V6.5Z" stroke="#37393D" stroke-width="1.5" stroke-linejoin="round"/>
                  <path d="M11 13.5H7V17.5H11V13.5Z" stroke="#37393D" stroke-width="1.5" stroke-linejoin="round"/>
                  <path d="M18 13.5H14V17.5H18V13.5Z" stroke="#37393D" stroke-width="1.5" stroke-linejoin="round"/>
                </svg>`,
  SaveAnswer: `<svg xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 25 24" fill="none">
                  <path d="M3.5 4.5C3.5 3.67158 4.17158 3 5 3H17.6407L21.5 6.60325V19.5C21.5 20.3285 20.8285 21 20 21H5C4.17158 21 3.5 20.3285 3.5 19.5V4.5Z" stroke="#37393D" stroke-width="1.5" stroke-linejoin="round"/>
                  <path d="M12.5042 3L12.5 6.6923C12.5 6.86225 12.2761 7 12 7H8C7.72385 7 7.5 6.86225 7.5 6.6923V3H12.5042Z" stroke="#37393D" stroke-width="1.5" stroke-linejoin="round"/>
                  <path d="M5 3H17.6407" stroke="#37393D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M7.5 13H17.5" stroke="#37393D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M7.5 17H12.5042" stroke="#37393D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>`,
  Submit: `<svg xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 25 24" fill="none">
              <path d="M12.5 22H5.50003C4.94775 22 4.50003 21.5523 4.50003 21V3C4.50003 2.44772 4.94775 2 5.50003 2H19.5C20.0523 2 20.5 2.44772 20.5 3V12" stroke="#37393D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M18.25 22V15.5" stroke="#37393D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M16 17.25L16.75 16.5L18.25 15L19.75 16.5L20.5 17.25" stroke="#37393D" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M8.49997 8H16.5" stroke="#37393D" stroke-width="1.5" stroke-linecap="round"/>
              <path d="M8.5 12H12.5" stroke="#37393D" stroke-width="1.5" stroke-linecap="round"/>
          </svg>`,
  Errors: `<svg xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 25 24" fill="none">
              <path d="M19.25 2H5.25C4.69771 2 4.25 2.44771 4.25 3V21C4.25 21.5523 4.69771 22 5.25 22H19.25C19.8023 22 20.25 21.5523 20.25 21V3C20.25 2.44771 19.8023 2 19.25 2Z" stroke="#333333" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M8.75 15H15.75" stroke="#333333" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M8.75 18H12.25" stroke="#333333" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M10.25 10.5L14.25 6.5" stroke="#333333" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M14.25 10.5L10.25 6.5" stroke="#333333" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>`,
}
const doFooter = [
  {
    name: "答题卡",
    iconName: icon.QuestionCard,
    type: "card"
  },
  {
    name: "保存答案",
    iconName: icon.SaveAnswer,
    type: "process"
  },
  {
    name: "立即提交",
    iconName: icon.Submit,
    type: "submit"
  }
];
const noProcessDoFooter = [
  {
    name: "答题卡",
    iconName: icon.QuestionCard,
    type: "card"
  },
  {
    name: "立即提交",
    iconName: icon.Submit,
    type: "submit"
  }
];
const report = [
  {
    name: "答题卡",
    iconName: icon.QuestionCard,
    type: "card"
  },
  {
    name: "错题",
    iconName: icon.Errors,
    activeIconName: icon.Errors,
    type: "wrong"
  }
];
const review = [
  {
    name: "答题卡",
    iconName: icon.QuestionCard,
    type: "card"
  },
  {
    name: "立即提交",
    iconName: icon.Submit,
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
      }  else if (this.mode === "report") {
        return report;
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
    },
  },
  methods: {
    check(type) {
      console.log(type);
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
  },
};
</script>
