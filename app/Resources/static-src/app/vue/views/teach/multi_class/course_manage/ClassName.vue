<template>
  <div class="class-name">
    {{ record.title }}
  </div>
</template>

<script>
export default {
  name: 'ClassName',

  props: {
    record: {
      type: Object,
      required: true,
      default() {
        return {}
      }
    }
  }
}
</script>
