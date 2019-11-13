<template>
  <div class="e-poster">
    <a v-if="poster.link.type == 'url' && feedback" :href="linkUrl">
      <img class="e-poster__img" v-lazy="poster.image.uri">
    </a>
    <img v-else class="e-poster__img" v-lazy="poster.image.uri" @click="jumpTo(link)">
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
    computed: {
      linkUrl() {
        const outterLink = this.poster.link.type == 'url' && this.feedback;
        if (!outterLink) return 'javascripts;';
        const url = this.poster.link.url && this.poster.link.url.trim();
        if (!url) return 'javascripts:;';
        const exsitsProtcol = (/^(\/\/)|(http:\/\/)|(https:\/\/)/).exec(url);
        if (!exsitsProtcol) return 'http://' + url;
        return url;
      },
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
        if (data.type === 'vip') {
          this.$router.push({
            path: `/vip`
          });
          return;
        }
      }
    }
  }
</script>
