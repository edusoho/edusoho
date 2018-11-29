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
      jumpTo(data) {
        if (!this.feedback) return;
        if (data.type === 'course' && data.target) {
          this.$router.push({
            path: `/course/${data.target.id}`
          });
          return;
        }
        if (data.type === 'classroom' && data.target) {
          this.$router.push({
            path: `/classroom/${data.target.id}`
          });
          return;
        }
        if (data.type == 'url' && data.url) {
          window.location.href = data.url;
          return;
        }
      }
    }
  }
</script>
