<template>
  <div class="e-poster">
    <a :href="posters.link.url">
      <img :src="posters.image.uri" class="poster-image">
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
  }
}
</script>
