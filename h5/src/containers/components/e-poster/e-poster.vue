<template>
  <div class="e-poster">
    <img class="e-poster__img" :src="poster.image.uri" @click="jumpTo(link.id)">
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
      jumpTo(courseId) {
        if (!this.feedback) return;

        const imgLink = this.poster.link;

        if (imgLink.type == 'course') {
          this.$router.push({
            path: `/course/${courseId}`
          });
        } else {
          window.location.href = imgLink.url;
        }
      }
    }
  }
</script>
