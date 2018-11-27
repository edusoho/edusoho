<template>
  <div class="e-tree-select">
    <div class="e-tree-select__items">
      <div class="e-tree-select__item"
        v-bind:class="{ active: selectedIndex === index && isActive }"
        v-for="(item, index) in selectItems"
        v-if="item.data"
        @click="toggle(item, index)"
      >{{ selectedText(item.data, index) }}</div>
    </div>

    <selectMenu
      v-show="isActive"
      v-model="proxyData"
      :menuContent="menuContent"
      @selectedChange="sendQuery"
    ></selectMenu>

    <div class="e-tree-select__background"
      v-show="isActive"
      @click="toggle()">
    </div>
  </div>
</template>

<script>
  import selectMenu from './e-select-menu/e-select-menu.vue';

  export default {
    model: {
      prop: 'selectedData',
      event: 'selectedChange'
    },
    components: {
      selectMenu
    },
    props: {
      selectItems: Array,
      selectedData: Object
    },
    data() {
      return {
        isActive: false,
        menuContent: {},
        selectedIndex: null,
      };
    },
    computed: {
      proxyData: {
        get() {
          return {...this.selectedData};
        },
        set() {
        }
      }
    },
    watch: {
      isActive(value) {
        this.$emit('selectToggled', value);
      }
    },
    methods: {
      toggle(item, index) {
        if(isNaN(index)) {
          this.isActive = false;
          this.selectedIndex = null;
          return
        }

        if(this.selectedIndex === index) {
          this.isActive = !this.isActive;
        } else {
          this.selectedIndex = index;
          this.menuContent = item;
          this.isActive = true;
        }
      },
      sendQuery(value) {
        this.$emit('selectedChange', value);
        this.toggle();
      },
      selectedText(value, index) {
        const TREE = {
          CATEGORY: 0,
          TYPE: 1,
          SORT: 2
        }
        for (let i = 0; i < value.length; i++) {
          if (index === TREE.CATEGORY) {
            if (value[i].id == this.selectedData.categoryId) return value[i].name
          } else if (index === TREE.TYPE) {
            if (value[i].type === this.selectedData.type) return value[i].text
          } else if (index === TREE.SORT) {
            if (value[i].type === this.selectedData.sort) return value[i].text
          }
        }
      }
    }
  }
</script>
