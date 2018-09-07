<template>
  <div class="e-poster">
    <img class="e-poster__img" :src="poster.image.uri" @click="jumpTo(link)">
  </div>
</template>

<script>
  export default {
    props: {
      poster: {
        type: Object,
        default: {}
      },
      feedback: {
        type: Boolean,
        default: true,
      }
    },
    data() {
      return {
        link: this.poster.link
      };
    },
    methods: {
      jumpTo(link) {
        if (!this.feedback) return;

        if (this.link.type == 'course' && this.link.target) {
          this.$router.push({
            path: `/course/${this.link.target.id}`
          });
          return;
        }
        if (this.link.type == 'url') {
          window.location.href = link.url;
          return;
        }
      }
    }
  }
</script>
