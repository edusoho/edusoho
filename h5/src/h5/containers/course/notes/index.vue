<template>
  <div class="notes">
    <keep-alive>
      <component
        :is="currentComponent"
        :note="currentNote"
        @change-current-component="changeCurrentComponent"
      />
    </keep-alive>
  </div>
</template>

<script>
import _ from 'lodash';
import List from './list.vue';
import Detail from './detail.vue';

export default {
  name: 'Notes',

  components: {
    List,
    Detail
  },

  data() {
    return {
      currentComponent: 'List',
      currentNote: {}
    }
  },

  watch: {
    currentComponent() {
      this.$emit('chang-tabs-status', this.currentComponent === 'List');
    }
  },

  methods: {
    changeCurrentComponent(params) {
      const { component, data } = params;

      _.assign(this, {
        currentComponent: component,
        currentNote: data
      });
    }
  }
}
</script>
