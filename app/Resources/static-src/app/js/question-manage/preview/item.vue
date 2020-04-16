<template>
  <div id="app" class="test-vue">
    <item-preview
      :item="item"
    ></item-preview>
  </div>
</template>

<script>
  export default {
    data() {
      return {
        item: JSON.parse($('[name=item]').val())
      }
    }
  }
</script>
