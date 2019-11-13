<template>
  <div class="e-poster">
    <a v-bind:href="posters.link.url">
      <img class="poster-image" v-bind:src="posters.image.uri">
    </a>
  </div>
</template>

<script>
  export default {
    props: {
      posters: {
        type: Array,
        default: []
      }
    },
  }
</script>
