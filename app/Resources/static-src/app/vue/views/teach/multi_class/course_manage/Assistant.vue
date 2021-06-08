<template>
  <div :class="[ellipsis ? 'assistant' : '', 'text-overflow']" ref="assistant">
    {{ assistants }}

    <a-tooltip v-if="ellipsis" class="assistant-all" placement="bottom">
      <template slot="title">
        <span>{{ assistants }}</span>
      </template>
      <a-icon type="ellipsis" />
    </a-tooltip>
  </div>
</template>

<script>
import _ from 'lodash';
export default {
  name: 'Assistant',

  props: {
    assistant: {
      type: Array,
      required: true,
      default() {
        return []
      }
    }
  },

  data() {
    return {
      ellipsis: false
    }
  },

  computed: {
    assistants() {
      if (!_.size(this.assistant)) return '- -';
      let temp = [];
      _.forEach(this.assistant, (assistant, index) => {
        if(assistant.nickname){
          temp.push(assistant.nickname);
        }else {
          temp.push(assistant);
        }
      });
      return _.join(temp, '、');
    },
  },

  mounted() {
    const assistantRef = this.$refs.assistant;
    this.ellipsis = assistantRef.scrollWidth > assistantRef.clientWidth;
  }
}
</script>

<style lang="less" scoped>
.assistant {
  position: relative;
  max-width: 100%;
  padding-right: 20px;

  .assistant-all {
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
  }
}
</style>
