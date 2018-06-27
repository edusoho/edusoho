<template>
  <div class="e-tree-select">
    <div class="e-tree-select__items">
      <div class="e-tree-select__item"
        v-bind:class="{ active: selectedIndex === index && isActive }"
        v-for="(item, index) in selectType"
        @click="toggle(item, index)"
      >{{ item.text }}</div>
    </div>

    <selectMenu v-show="isActive" :menuContent="menuContent"></selectMenu>

    <div class="e-tree-select__background"
      v-show="isActive"
      @click="toggle()">
    </div>
  </div>
</template>

<script>
  import selectMenu from './e-select-menu/e-select-menu.vue';

  export default {
    components: {
      selectMenu
    },
    props: {
      selectType: {
        type: Array,
        default: []
      }
    },
    data() {
      return {
        isActive: false,
        activeId: null,
        menuContent: {},
        selectedIndex: null,
      };
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
    }
  }
</script>
