<template>
  <div class="e-tree-selects">
    <!-- 分类选择 -->
    <div>
      <div class="e-tree-warp">
        <div class="e-tree-scroll" ref="e-tree-scroll">
          <div
            v-for="(item,index) in categories"
            :key="index"
            :ref="`treemenu${item.id}`"
            :class="{ activeTag: (item.id==categoriesId) }"
            @click="selectTag(item.id)"
          >{{item.name}}</div>
          <div v-if="treeMenuLevel>1" class="e-tree-more" @click="openMore()">
            <i class="iconfont icon-more"></i>
          </div>
        </div>
      </div>

      <tree-menu
        v-show="openMenu"
        v-model="proxyData"
        :categoriesId="categoriesId"
        :categories="categories"
        @selectedChange="treeMenuSelect"
      />
    </div>

    <!-- 下拉选择 -->
    <div class="e-tree-select__list">
      <div class="e-tree-select__items">
        <div
          v-if="item.data && index>0"
          v-for="(item, index) in selectItems"
          :key="index"
          :class="['e-tree-select__item' , (selectedIndex === index && isActive)?'active':'' ]"
          @click="toggle(item, index)"
        >{{ selectedText(item.data, index) }}</div>

        <div class="showfree" v-show="type=='course'">
          仅显示免费
          <van-switch v-model="showFree" size="12px" active-color="#03C777" />
        </div>
      </div>

      <selectMenu
        v-show="isActive"
        v-model="proxyData"
        :menu-content="menuContent"
        @selectedChange="sendQuery"
      />
      <div v-show="isActive" class="e-tree-select__mask" @click="toggle()" />
    </div>
  </div>
</template>

<script>
import selectMenu from "./e-select-menu/e-select-menu.vue";
import treeMenu from "./e-tree-menu/e-tree-menu";
const mo = function(e) {
  e.preventDefault();
};
export default {
  components: {
    selectMenu,
    treeMenu
  },
  model: {
    prop: "selectedData",
    event: "selectedChange"
  },
  props: {
    selectItems: Array,
    selectedData: Object,
    categories: Array,
    treeMenuLevel: Number,
    type: String
  },
  data() {
    return {
      showFree: false,
      isActive: false,
      openMenu: false,
      categoriesId: this.selectedData.categoryId || 0, //最父级的分类id，默认是name="全部"的id
      menuContent: {},
      selectedIndex: null
    };
  },
  computed: {
    proxyData: {
      get() {
        return { ...this.selectedData };
      },
      set() {}
    }
  },
  watch: {
    isActive(value) {
      this.$emit("selectToggled", value);
    },
    showFree(value, oldValue) {
      if (value) {
        this.$set(this.selectedData, "price", "0");
      } else {
        this.$delete(this.selectedData, "price");
      }
      this.$emit("selectedChange", this.selectedData);
    }
  },
  methods: {
    // 滑动部分选择
    selectTag(id) {
      if (this.categoriesId === Number(id)) {
        return;
      }
      this.categoriesId = Number(id);
      this.selectedData.categoryId = Number(id);
      this.$emit("selectedChange", this.selectedData);
    },

    toggle(item, index) {
      this.move();
      if (isNaN(index)) {
        this.isActive = false;
        this.selectedIndex = null;
        return;
      }

      if (this.selectedIndex === index) {
        this.isActive = !this.isActive;
      } else {
        this.selectedIndex = index;
        this.menuContent = item;
        this.isActive = true;
      }

      if (this.isActive) {
        this.stop();
      }
    },

    treeMenuSelect(value, categoriesId) {
      this.categoriesId = categoriesId;
      this.openMenu = false;
      this.treeScroll(categoriesId);
      this.$emit("selectedChange", value);
      this.move();
    },
    treeScroll(categoriesId) {
      const treemenu = this.$refs[`treemenu${categoriesId}`];
      const treescrollWarp = this.$refs[`e-tree-scroll`];
      treescrollWarp.scrollLeft =
        treemenu[0].offsetLeft - treescrollWarp.offsetLeft;
    },
    sendQuery(value) {
      this.$emit("selectedChange", value);
      this.toggle();
    },

    selectedText(value, index) {
      const TREE = {
        CATEGORY: 0,
        TYPE: 1,
        SORT: 2
      };
      for (let i = 0; i < value.length; i++) {
        if (index === TREE.CATEGORY) {
          if (value[i].id == this.selectedData.categoryId) return value[i].name;
        } else if (index === TREE.TYPE) {
          if (value[i].type === this.selectedData.type) return value[i].text;
        } else if (index === TREE.SORT) {
          if (value[i].type === this.selectedData.sort) return value[i].text;
        }
      }
    },
    openMore() {
      this.isActive = false;
      this.openMenu = true;
      this.stop();
    },
    /***开启滑动限制***/
    stop() {
      document.body.style.overflow = "hidden";
      document.addEventListener("touchmove", mo, false); //禁止页面滑动
    },
    /***取消滑动限制***/
    move() {
      document.body.style.overflow = ""; //出现滚动条
      document.removeEventListener("touchmove", mo, false);
    }
  }
};
</script>

<style>
</style>