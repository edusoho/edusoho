<template>
  <div class="course-detail">
    <join-before v-if="!joinStatus"></join-before>    
    <join-after v-else></join-after>
  </div>
</template>

<script>
  import joinAfter from './joinAfter.vue';
  import joinBefore from './joinBefore.vue';

  export default {
    components: {
      joinAfter,
      joinBefore
    },
    data() {
      return {
        joinStatus: false, // joinAfter = true
      };
    },
  }
</script>
