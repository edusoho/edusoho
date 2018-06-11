<template>
  <div class="e-promotion">
    <img class="e-promotion__img" v-bind:src="promotion.image" @click="jumpTo(link.id)">
  </div>
</template>

<script>
  export default {
    props: {
      promotion: {
        type: Object,
        default: {}
      }
    },
    data () {
      return {
        link: this.promotion.link
      };
    },
    methods: {
      jumpTo(courseId) {
        this.$router.push({
          path: `/course/${courseId}`
        });
      }
    }
  }
</script>
