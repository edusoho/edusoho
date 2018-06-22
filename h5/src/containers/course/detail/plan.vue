<template>
  <ul class="course-detail__plan">
    <li v-for="item in items" 
      @click="handleClick(item)" 
      :class="{active: item.active}">三月班</li>
  </ul>
</template>
<script>
export default {
  data() {
    return {
      items: [{
        name: '三月班',
        active: true,
      },{
        name: '三月班',
        active: false,
      },{
        name: '三月班',
        active: false,
      },{
        name: '三月班',
        active: false,
      }]
    }
  },
  methods: {
    handleClick(item){
      this.items.map(item => item.active = false);
      item.active = true;
    }
  }
}
</script>
