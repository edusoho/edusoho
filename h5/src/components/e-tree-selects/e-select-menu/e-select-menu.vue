<template>
  <div class="e-menus">  
    <div  class="e-menu__line">
      <div
        v-for="(item , index) in menuContent.data"
        :key="index"
        :class="judgeIsSelected(item, menuContent.type)"
        class="e-menu__item"
        @click="itemSelect(item, menuContent.type)"
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
      this.queryData[query] = item.type
      // 更新数据
      this.$emit('selectedChange', this.queryData)
    },
    judgeIsSelected(item, type) {
      const isSelected = this.queryData[this.queryForm[type]] === item.type
      if (isSelected) return 'selected'
    }
  }
}

</script>
