<template>
  <div class="stem">
    <div class="stem-order">{{ order }}</div>
    <div v-html="formateQuestionStem" />
  </div>
</template>

<script>
export default {
  props: {
    order: {
      type: [String, Number],
      required: true
    },

    stem: {
      type: String,
      required: true
    }
  },

  computed: {
    formateQuestionStem() {
      const text = this.stem;
      const reg = /\[\[\]\]/g;
      if (!text.match(reg)) {
        return text;
      }
      let index = 1;
      return text.replace(reg, function() {
        return `<span class="stem-fill-blank ph16">(${index++})</span>`;
      });
    }
  }
}
</script>

<style lang="less" scoped>
.stem {
  position: relative;

  /deep/ table {
    width: 100%;
    border-collapse: collapse;
    text-align: center;

    td {
      border: 1px solid #333;
    }

    th {
      border: 1px solid #333;
    }
  }

  .stem-order {
    position: absolute;
    left: -30px;
    top: 0;
  }

  /deep/ p {
    margin: 0;
  }

  /deep/ img {
    max-width: 100%!important;
  }

  /deep/ .stem-fill-blank {
    padding-bottom: 2px;
    line-height: 20px;
    border-bottom: 1px solid #999;
    color: #aaa;
  }
}
</style>
