<template>
  <div class="e-menu__trees">
    <div class="e-menu__items level-one">
      <div
        v-for="(item,index) in categories"
        :key="index"
        :class="[item.id == fristId ? 'treeSelected' : '']"
        class="e-menu__item"
        @click="itemSelect(item, 'levelOne')"
      >{{ item.name }}</div>
    </div>
    <div class="e-menu__items level-two" v-if="secondLevel.length>0">
      <div
        v-for="(item,index) in secondLevel"
        :key="index"
        :class="[item.id == secondId ? 'treeSelected' : '']"
        class="e-menu__item"
        @click="itemSelect(item,  'levelTwo')"
      >{{ item.name }}</div>
    </div>
    <div class="e-menu__items level-three" v-if="thirdLevel.length>0">
      <div
        v-for="(item,index) in thirdLevel"
        :key="index"
        :class="[item.id == thirdId ? 'treeSelected' : '']"
        class="e-menu__item"
        @click="itemSelect(item, 'levelThree')"
      >{{ item.name }}</div>
    </div>
  </div>
</template>

<script>
export default {
  name: "e-tree-menu",
  model: {
    prop: "selectedData",
    event: "selectedChange"
  },
  props: {
    categories: Array,
    selectedData: Object,
    categoriesId: Number
  },
  data() {
    return {
      secondLevel: [],
      thirdLevel: [],
      fristId: 0, //默认选中全部
      secondId: null,
      thirdId: null,
      isReadyEmit: false
    };
  },
  computed: {
    queryData: {
      get() {
        return { ...this.selectedData };
      },
      set() {}
    }
  },
  watch: {
    categoriesId: function(value) {
      this.resetData();
      this.fristId = value;
      this.selectedActive();
    }
  },
  methods: {
    itemSelect(item, level) {
      switch (level) {
        case "levelOne":
          this.resetData();
          this.fristId = Number(item.id);
          if (item.children.length) {
            this.secondLevel = this.insertAll(item.children);
          } else {
            this.sendData(item.id);
          }
          break;
        case "levelTwo":
          this.thirdId = null;
          this.thirdLevel = [];
          this.secondId = Number(item.id);
          // 如果是全部，则筛选的分类id为父级的分类id
          if (item.name === "全部") {
            this.sendData(this.fristId);
          } else if (item.children.length) {
            this.thirdLevel = this.insertAll(item.children);
          } else {
            this.sendData(item.id);
          }
          break;
        case "levelThree":
          this.thirdId = Number(item.id);
          if (item.name === "全部") {
            this.sendData(this.secondId);
          } else {
            this.sendData(item.id);
          }
          break;
      }
    },
    insertAll(children) {
      if (children[0].name !== "全部") {
        children.unshift({ name: "全部", id: "0", children: [] });
      }
      return children;
    },
    resetData() {
      this.secondLevel = this.thirdLevel = [];
      this.fristId = this.secondId = this.thirdId = null;
    },
    selectedActive() {
      this.categories.forEach(item => {
        if (item.id == this.fristId && item.children.length) {
          this.secondLevel = this.insertAll(item.children);
          this.secondId = 0;
        }
      });
    },
    sendData(id) {
      this.queryData.categoryId = Number(id);
      this.$emit("selectedChange", this.queryData, this.fristId);
    }
  }
};
</script>

<style>
</style>