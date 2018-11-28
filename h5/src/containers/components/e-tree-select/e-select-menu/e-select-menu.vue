<template>
  <div class="e-menu">
    <!-- select-tree -->
    <div class="e-menu__tree" v-if="menuContent.moduleType == 'tree'">
      <!-- first-level -->
      <div class="e-menu__items level-one">
        <div class="e-menu__item"
          :class="[item.id == queryData.categoryId ? 'selected' : '']"
          v-for="item in menuContent.data"
          @click="itemSelect(item, menuContent.type, 'levelOne')"
        >{{ item.name }}</div>
      </div>
      <!-- second-level -->
      <div class="e-menu__items level-two">
        <div class="e-menu__item"
          :class="[item.id == queryData.categoryId ? 'selected' : '']"
          v-for="item in secondLevel"
          @click="itemSelect(item, menuContent.type, 'levelTwo')"
        >{{ item.name || item.text }}</div>
      </div>
      <!-- third-level -->
      <div class="e-menu__items level-three">
        <div class="e-menu__item"
          :class="[item.id == queryData.categoryId ? 'selected' : '']"
          v-for="item in thirdLevel"
          @click="itemSelect(item, menuContent.type, 'levelThree')"
        >{{ item.name || item.text }}</div>
      </div>
    </div>
    <!-- line -->
    <div class="e-menu__line" v-if="menuContent.moduleType == 'normal'">
      <div class="e-menu__item"
        :class="judgeIsSelected(item, menuContent.type)"
        v-for="item in menuContent.data"
        @click="itemSelect(item, menuContent.type)"
      >{{ item.name || item.text }}</div>
    </div>
  </div>
</template>

<script>
  import Api from '@/api';

  export default {
    model: {
      prop: 'selectedData',
      event: 'selectedChange'
    },
    props: {
      menuContent: Object,
      selectedData: Object
    },
    data() {
      return {
        secondLevel: [],
        thirdLevel: [],
        queryForm: {
          courseType: 'type',
          category: 'categoryId',
          sort: 'sort'
        },
        isReadyEmit: false
      };
    },
    computed: {
      queryData: {
        get() {
          return {...this.selectedData};
        },
        set() {
        }
      }
    },
    methods: {
      itemSelect(item, type, level) {
        console.log(item,88)
        const query = this.queryForm[type];
        this.isReadyEmit = false;
        if (query === 'categoryId') {
          switch(level) {
            case 'levelOne':
              if (item.children.length) {
                this.secondLevel = item.children;
              } else {
                this.queryData.categoryId = Number(item.id)
                this.isReadyEmit = true;
              }
              break;
            case 'levelTwo':
              if (item.children.length) {
                this.thirdLevel = item.children;
              } else {
                this.queryData.categoryId = Number(item.id)
                this.isReadyEmit = true;
              }
              break;
            case 'levelThree':
              this.queryData.categoryId = Number(item.id)
              this.isReadyEmit = true;
              break;
          }
        } else {
          this.queryData[query] = item.type
          this.isReadyEmit = true;
        }
        // 更新数据
        if (this.isReadyEmit) this.$emit('selectedChange', this.queryData);
      },
      judgeIsSelected(item, type) {
        const isSelected = this.queryData[this.queryForm[type]] === item.type;
        if (isSelected) return 'selected'
      },
    },
  }

</script>
