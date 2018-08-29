<template>
  <div class="e-promotion">
    <img class="e-promotion__img" v-bind:src="promotion.image.uri" @click="jumpTo(link.id)">
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
    data() {
      return {
        link: this.promotion.link
      };
    },
    methods: {
      jumpTo(courseId) {
        const imgLink = this.promotion.link.type;
        if (imgLink == 'course') {
          this.$router.push({
            path: `/course/${courseId}`
          });
        } else {
          window.location.href = this.promotion.link.url;
        }
      }
    }
  }
</script>
