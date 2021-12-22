<template>
  <a-modal
    :visible="visible"
    :width="900"
    :ok-text="'site.btn.save' | trans"
    @cancel="handleCancel"
    @ok="handleOk"
  >
    <template #title>
      {{ 'decorate.choose_a_course' | trans }}
      <span class="modal-title-tips">{{ 'decorate.show_only_published_courses' | trans }}</span>
    </template>

    <div>
      {{ 'decorate.choose_a_course' | trans }}：
      <a-select
        show-search
        :placeholder="'decorate.search_course' | trans"
        style="width: 300px"
        :default-active-first-option="false"
        :show-arrow="false"
        :filter-option="false"
        allow-clear
        :not-found-content="null"
        @search="onSearch"
        @change="handleChange"
      >
        <a-select-option v-for="course in data" :key="course.id">
          {{ course.title || course.courseSetTitle }}
        </a-select-option>
      </a-select>
    </div>

    <a-table
      class="mt16"
      :columns="columns"
      :row-key="record => record.id"
      :pagination="false"
      :data-source="selectList"
    >
      <span slot="action" slot-scope="text, record">
        <a class="ant-dropdown-link" @click="handleRemove(record.key)">{{ 'decorate.remove' | trans }}</a>
      </span>
    </a-table>
  </a-modal>
</template>
<script>
import _ from 'lodash';
import { Course } from 'common/vue/service/index.js';

const columns = [
  {
    title: Translator.trans('decorate.course_title'),
    dataIndex: 'title',
    width: '40%',
    customRender: function(text, record) {
      return text ? text : record.courseSetTitle;
    }
  },
  {
    title: Translator.trans('decorate.commodity_price'),
    dataIndex: 'price',
    width: '15%',
    customRender: function(text) {
      return `${text} ${Translator.trans('cny')}`;
    }
  },
  {
    title: Translator.trans('decorate.creation_time'),
    dataIndex: 'createdTime',
    width: '30%',
    customRender: function(text) {
      return moment(text).format('YYYY-MM-DD HH:mm');
    }
  },
  {
    title: Translator.trans('decorate.operate'),
    width: '15%',
    scopedSlots: { customRender: 'action' }
  }
];

export default {
  name: 'SeleteCourseModal',

  data() {
    return {
      visible: false,
      data: [],
      selectList: [],
      keyword: '',
      columns
    }
  },

  methods: {
    showModal() {
      this.visible = true;
      this.keyword = '';
    },

    handleCancel() {
      this.visible = false;
    },

    onSearch: _.debounce(function(value) {
      this.keyword = value;
      this.fetch();
    }, 200),

    async fetch() {
      const params = {
        sort: '-createdTime',
        title: this.keyword
      };

      const { data } = await Course.searchCourses(params);

      this.data = data;
    },

    handleChange(value) {
      _.forEach(this.data, item => {
        if (item.id === value) {
          this.selectList.push(item);
          return false;
        }
      });
    },

    handleRemove(value) {
      this.selectList.splice(value, 1);
    },

    handleOk() {
      this.$emit('update-items', this.selectList);
      this.visible = false;
    }
  },
};
</script>

<style lang="less" scoped>
.modal-title-tips {
  margin-left: 10px;
  font-size: 12px;
  color: #919191;
}
</style>
