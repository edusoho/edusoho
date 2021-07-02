<template>
  <a-table
    :columns="columns"
    :row-key="record => record.itemId"
    :data-source="data"
    :pagination="pagination"
    :loading="loading"
    @change="handleTableChange"
  >
    <template slot="order" slot-scope="order, record, index">
      {{ (pagination.current - 1) * 10 + index + 1 }}
    </template>

    <template slot="itemTitle" slot-scope="itemTitle">
      <span class="stem" v-html="formateQuestionStem(itemTitle)"></span>
    </template>

    <template slot="sourceType" slot-scope="sourceType">
      {{ formateQuestionSource(sourceType) }}
    </template>

    <template slot="actions" slot-scope="actions, record, index">
      <a-button type="link" @click="handleClickViewDetails(record.itemId, (pagination.current - 1) * 10 + index + 1)">查看详情</a-button>
    </template>
  </a-table>
</template>

<script>
import _ from 'lodash';

const columns = [
  {
    title: '',
    dataIndex: 'order',
    width: '10%',
    scopedSlots: { customRender: 'order' }
  },
  {
    title: '题目',
    dataIndex: 'itemTitle',
    width: '30%',
    scopedSlots: { customRender: 'itemTitle' }
  },
  {
    title: '任务名称',
    dataIndex: 'courseName',
    width: '15%'
  },
  {
    title: '来源',
    dataIndex: 'sourceType',
    width: '20%',
    scopedSlots: { customRender: 'sourceType' }
  },
  {
    title: '答错人次',
    dataIndex: 'wrong_times',
    width: '15%'
  },
  {
    title: '操作',
    width: '10%',
    scopedSlots: { customRender: 'actions' }
  },
];

export default {
  name: 'WrongQuestionTable',

  props: {
    data: {
      type: Array,
      required: true
    },

    pagination: {
      type: Object,
      required: true
    },

    loading: {
      type: Boolean,
      required: true
    }
  },

  data() {
    return {
      columns,
      sources: {
        testpaper: '考试任务',
        homework: '作业任务',
        exercise: '练习任务'
      }
    }
  },

  methods: {
    formateQuestionStem(text) {
      const reg = /\[\[\]\]/g;
      if (!text.match(reg)) {
        return text;
      }
      let index = 1;
      return text.replace(reg, function() {
        return `<span class="stem-fill-blank ph16">(${index++})</span>`;
      });
    },

    formateQuestionSource(sources) {
      const result = _.map(sources, sourceType => {
        return this.sources[sourceType];
      });

      return _.join(result, '、');
    },

    handleTableChange(pagination) {
      this.$emit('event-communication', {
        type: 'pagination',
        data: pagination
      });
    },

    handleClickViewDetails(id, order) {
      this.$emit('event-communication', {
        type: 'view-detail',
        data: {
          id,
          order
        }
      });
    }
  }
}
</script>

<style lang="less" scoped>

/deep/ .stem p {
  margin: 0;
}

/deep/ .stem-fill-blank {
  padding-bottom: 2px;
  line-height: 20px;
  border-bottom: 1px solid #999;
  color: #aaa;
}
</style>
