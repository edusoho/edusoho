<template>
  <a-modal
    :visible="visible"
    :width="900"
    @cancel="handleCancel"
  >
    <template #title>
      {{ 'decorate.choose_a_course' | trans }}
      <span class="modal-title-tips">{{ 'decorate.show_only_published_courses' | trans }}</span>
    </template>

    <template #footer>
      <a-button key="back" @click="handleCancel">
        {{ 'site.cancel' | trans }}
      </a-button>
    </template>

    <div>
      <a-input-search
        v-model="keyword"
        :placeholder="'decorate.search_course' | trans"
        style="width: 240px;"
        allow-clear
        @search="onSearch"
      />
    </div>

    <a-table
      class="mt16"
      :columns="columns"
      :row-key="record => record.id"
      :data-source="data"
      :pagination="pagination"
      :loading="loading"
      @change="handleTableChange"
    >
      <span slot="action" slot-scope="text, record">
        <a class="ant-dropdown-link" @click="handleSelect(record)">{{ 'decorate.choose' | trans }}</a>
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
  name: 'CourseLinkModal',

  data() {
    return {
      visible: false,
      data: [],
      keyword: '',
      loading: false,
      columns,
      pagination: {
        pageSize: 10,
        current: 1,
        hideOnSinglePage: true
      }
    }
  },

  methods: {
    showModal() {
      this.visible = true;
      this.keyword = '';
      this.pagination.current = 1;
      this.fetch();
    },

    handleCancel() {
      this.visible = false;
    },

    handleSelect(record) {
      const { displayedTitle, courseSet, id, title  } = record;
      const params = {
        type: 'course',
        target: {
          displayedTitle,
          courseSetId: courseSet.id,
          id,
          title
        },
        url: ''
      };
      this.$emit('update-link', params);
      this.handleCancel();
    },

    onSearch() {
      this.pagination.current = 1;
      this.fetch();
    },

    handleTableChange(pagination) {
      const { current } = pagination;
      _.assign(this.pagination, {
        current
      });
      this.fetch();
    },

    async fetch() {
      this.loading = true;

      const { pageSize, current } = this.pagination;
      const params = {
        limit: pageSize,
        offset: pageSize * (current - 1),
        sort: '-createdTime',
        courseSetTitle: this.keyword
      };

      const { data, paging: { total } } = await Course.searchCourses(params);
      const pagination = { ...this.pagination };
      pagination.total = total;

      _.assign(this, {
        loading: false,
        data,
        pagination
      });
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
