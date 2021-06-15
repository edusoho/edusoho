<template>
  <div>
    <a-input-search
      allowClear
      placeholder="请输入课程名称"
      style="width: 224px; margin-top: 16px;"
      @search="onSearch"
    />

    <template>
      <list-item />
    </template>

    <a-pagination
      class="text-center"
      style="margin-top: 16px;"
      :hide-on-single-page="true"
      v-model="pagination.current"
      :total="pagination.total"
      @change="onChange"
    />
  </div>
</template>

<script>
import ListItem from './ListItem.vue';

export default {
  components: {
    ListItem
  },

  data() {
    return {
      pagination: {}
    }
  },

  methods: {
    onSearch(value) {
      console.log(value);
    },

    onChange(current) {
      console.log(current);
    }
  }
}
</script>
