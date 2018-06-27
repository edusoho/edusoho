<template>
  <div class="e-menu">
    <!-- select-tree -->
    <div class="e-menu__tree" v-if="menuContent.type == 'tree'">
      <!-- first-level -->
      <div class="e-menu__items level-one">
        <div class="e-menu__item selected"
          v-for="item in menuContent.children"
          @click="itemSelect(item, 'levelOne')"
        >{{ item.text }}</div>
      </div>
      <!-- second-level -->
      <div class="e-menu__items level-two">
        <div class="e-menu__item selected"
          v-for="item in secondLevel"
          @click="itemSelect(item, 'levelTwo')"
        >{{ item.text }}</div>
      </div>
      <!-- third-level -->
      <div class="e-menu__items level-three">
        <div class="e-menu__item"
          v-for="item in thirdLevel"
        >{{ item.text }}</div>
      </div>
    </div>
    <!-- line -->
    <div class="e-menu__line" v-if="menuContent.type == 'normal'">
      <div class="e-menu__item selected">推荐</div>
      <div class="e-menu__item">最新</div>
      <div class="e-menu__item">最热</div>
    </div>
  </div>
</template>

<script>
  export default {
    props: {
      menuContent: {
        type: Object,
        default: {}
      }
    },
    data() {
      return {
        secondLevel: [],
        thirdLevel: [],
      };
    },
    watch: {

    },
    methods: {
      itemSelect(item, level) {
        switch(level) {
          case 'levelOne':
            this.secondLevel = item.children;
            break;
          case 'levelTwo':
            this.thirdLevel = item.children;
        }
      }
    }
  }

</script>
