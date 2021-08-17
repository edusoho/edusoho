<template>
  <div class="reviews">
    <keep-alive>
      <component
        :is="currentComponent"
        @change-current-component="changeCurrentComponent"
      />
    </keep-alive>
  </div>
</template>

<script>
import _ from 'lodash';
import List from './list.vue';
import Create from './create.vue';

export default {
  name: 'Reviews',

  components: {
    List,
    Create
  },

  data() {
    return {
      currentComponent: 'List'
    }
  },

  watch: {
    currentComponent() {
      this.$emit('chang-tabs-status', this.currentComponent === 'List');
    }
  },

  methods: {
    changeCurrentComponent(params) {
      const { component } = params;

      _.assign(this, {
        currentComponent: component
      });
    }
  }
}
</script>
