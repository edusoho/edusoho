<template>
  <a-table
    :columns="columns"
    :row-key="record => record.id"
    :data-source="data"
    :pagination="pagination"
    :loading="loading"
    @change="handleTableChange"
  >
    <template slot="answer_time" slot-scope="answer_time">
      {{ $dateFormat(answer_time, 'YYYY-MM-DD HH:mm:ss') }}
    </template>

    <template slot="answer" slot-scope="answer">
      <span :title="formatAnswer(answer)">{{ formatAnswer(answer) }}</span>
    </template>
  </a-table>
</template>

<script>
import _ from 'lodash';

const columns = [
  {
    title: '用户名',
    dataIndex: 'user_name',
    width: '15%'
  },
  {
    title: '做错频次',
    dataIndex: 'wrong_times',
    width: '15%'
  },
  {
    title: '答题时间',
    dataIndex: 'answer_time',
    width: '30%',
    scopedSlots: { customRender: 'answer_time' }
  },
  {
    title: '答题结果',
    dataIndex: 'answer',
    ellipsis: true,
    width: '40%',
    scopedSlots: { customRender: 'answer' }
  }
];

export default {
  name: 'ViewWrongQuestionDetailTable',

  props: {
    data: {
      type: Array,
      required: true
    },

    loading: {
      type: Boolean,
      required: true
    },

    pagination: {
      type: Object,
      required: true
    }
  },

  data() {
    return {
      columns
    }
  },

  methods: {
    formatAnswer(answer) {
      if (!_.size(answer)) {
        return '未作答';
      }
      return _.join(answer, ',');
    },

    handleTableChange(pagination) {
      this.$emit('event-communication', {
        type: 'pagination',
        data: pagination
      });
    }
  }
};
</script>
