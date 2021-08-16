<template>
  <div class="discussion">
    <component
      :is="currentComponent"
      :id="currentDiscussionId"
      :type="type"
      @change-current-component="changeCurrentComponent"
    />
  </div>
</template>

<script>
import _ from 'lodash';
import List from './list.vue';
import Detail from './detail.vue';
import Create from './create.vue';

export default {
  name: 'Discussion',

  components: {
    List,
    Detail,
    Create
  },

  props: {
    type: {
      type: String,
      required: true
    }
  },

  data() {
    return {
      currentComponent: 'List',
      currentDiscussionId: 0
    }
  },

  watch: {
    currentComponent() {
      this.$emit('chang-tabs-status', this.currentComponent === 'List');
    }
  },

  methods: {
    changeCurrentComponent(params) {
      const { component, id } = params;

      _.assign(this, {
        currentComponent: component,
        currentDiscussionId: id
      });
    }
  }
}
</script>
