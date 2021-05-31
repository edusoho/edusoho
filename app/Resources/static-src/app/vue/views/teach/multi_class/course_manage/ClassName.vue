<template>
  <div class="text-overflow">
    {{ className }}
    <br>
    <a-tag v-if="record.status != 'published'" style="margin-top: 4px;">未发布</a-tag>
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
  },

  computed: {
    className() {
      const { chapterTitle, unitTitle, number, title } = this.record;
      let className = `${number}.${title}`;

      if (unitTitle) {
        className = `${unitTitle} ${className}`;
      }

      if (chapterTitle) {
        className = `${chapterTitle} - ${className}`;
      }

      return className;
    }
  }
}
</script>
