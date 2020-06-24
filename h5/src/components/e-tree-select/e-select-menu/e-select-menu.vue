<template>
  <div class="e-menu">
    <!-- select-tree -->
    <div v-if="menuContent.moduleType == 'tree'" class="e-menu__tree">
      <!-- first-level -->
      <div class="e-menu__items level-one">
        <div
          v-for="(item,levelOneIndex) in menuContent.data"
          :class="[item.id == queryData.categoryId ? 'selected' : '']"
          class="e-menu__item"
          @click="itemSelect(item, menuContent.type, 'levelOne')"
          :key="'levelOne'+levelOneIndex"
        >{{ item.name }}</div>
      </div>
      <!-- second-level -->
      <div class="e-menu__items level-two">
        <div
          v-for="(item,levelTwoIndex) in secondLevel"
          :class="[item.id == queryData.categoryId ? 'selected' : '']"
          class="e-menu__item"
          @click="itemSelect(item, menuContent.type, 'levelTwo')"
          :key="'levelTwo'+levelTwoIndex"
        >{{ item.text }}</div>
      </div>
      <!-- third-level -->
      <div class="e-menu__items level-three">
        <div
          v-for="(item,levelThreeIndex) in thirdLevel"
          :class="[item.id == queryData.categoryId ? 'selected' : '']"
          class="e-menu__item"
          @click="itemSelect(item, menuContent.type, 'levelThree')"
          :key="'levelThree'+levelThreeIndex"
        >{{ item.text }}</div>
      </div>
    </div>
    <!-- line -->
    <div v-if="menuContent.moduleType == 'normal'" class="e-menu__line">
      <div
        v-for="(item,lineIndex) in menuContent.data"
        :class="judgeIsSelected(item, menuContent.type)"
        class="e-menu__item"
        @click="itemSelect(item, menuContent.type)"
         :key="'line'+lineIndex"
      >{{ item.text }}</div>
    </div>
  </div>
</template>

<script>
import Api from '@/api'

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
    }
  },
  computed: {
    queryData: {
      get() {
        return { ...this.selectedData }
      },
      set() {
      }
    }
  },
  methods: {
    itemSelect(item, type, level) {
      const query = this.queryForm[type]
      this.isReadyEmit = false
      if (query === 'categoryId') {
        switch (level) {
          case 'levelOne':
            // 暂不展示多级分类
            // if (item.children) {
            //   this.secondLevel = item.children;
            // } else {
            //   this.queryData.categoryId = Number(item.id)
            //   this.isReadyEmit = true;
            // }
            this.queryData.categoryId = Number(item.id)
            this.isReadyEmit = true
            break
          case 'levelTwo':
            if (item.children) {
              this.thirdLevel = item.children
            } else {
              this.queryData.categoryId = Number(item.id)
              this.isReadyEmit = true
            }
            break
          case 'levelThree':
            this.queryData.categoryId = Number(item.id)
            this.isReadyEmit = true
            break
        }
      } else {
        this.queryData[query] = item.type
        this.isReadyEmit = true
      }
      // 更新数据
      if (this.isReadyEmit) this.$emit('selectedChange', this.queryData)
    },
    judgeIsSelected(item, type) {
      const isSelected = this.queryData[this.queryForm[type]] === item.type
      if (isSelected) return 'selected'
    }
  }
}

</script>
