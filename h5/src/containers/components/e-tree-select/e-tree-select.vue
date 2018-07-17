<template>
  <div class="e-tree-select">
    <div class="e-tree-select__items">
      <div class="e-tree-select__item"
        v-bind:class="{ active: selectedIndex === index && isActive }"
        v-for="(item, index) in selectItems"
        @click="toggle(item, index)"
      >{{ item.text }}</div>
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
        proxyData: {}
      };
    },
    watch: {
      selectedData() {
        this.proxyData = this.selectedData;
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
      }
    }
  }
</script>
