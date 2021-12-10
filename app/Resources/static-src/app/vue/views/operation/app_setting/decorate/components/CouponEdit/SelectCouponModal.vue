<template>
  <a-modal
    :visible="visible"
    :width="900"
    @ok="handleOk"
    @cancel="handleCancel"
  >
    <template #title>
      选择优惠券
      <span class="modal-title-tips">仅显示未过期的优惠券</span>
    </template>

    <div>
      <a-input-search
        v-model="keyword"
        placeholder="搜索优惠卷"
        style="width: 240px;"
        allow-clear
        @search="onSearch"
      />
    </div>

    <a-table
      class="mt16"
      :columns="columns"
      :row-selection="{
        selectedRowKeys: selectedRowKeys,
        onChange: onSelectChange,
        onSelect: onSelect,
        onSelectAll: onSelectAll
      }"
      :row-key="record => record.id"
      :data-source="data"
      :pagination="pagination"
      :loading="loading"
      @change="handleTableChange"
    />
  </a-modal>
</template>
<script>
import _ from 'lodash';
import { Coupon } from 'common/vue/service/index.js';

const columns = [
  {
    title: '优惠卷名称',
    dataIndex: 'name',
    width: '20%'
  },
  {
    title: '前缀',
    dataIndex: 'prefix',
    width: '15%'
  },
  {
    title: '优惠内容',
    width: '30%',
    customRender: function(record) {
      const { type, rate, targetDetail: { numType, product } } = record;

      let discountType = '折扣';
      let text = '折';
      let targetType = '全部商品';

      if (numType === 'single') {
        switch (product) {
          case 'course':
          case 'classroom':
            targetType = '指定商品';
            break;
          case 'vip':
            targetType = '指定会员';
            break;
          default:
            targetType = '';
        }
      } else if (numType === 'all') {
        switch (product) {
          case 'course':
            targetType = '全部课程';
            break;
          case 'classroom':
            targetType = '全部班级';
            break;
          case 'all':
            targetType = '全部商品';
            break;
          case 'vip':
            targetType = '全部会员';
            break;
          default:
            targetType = '';
        }
      } else {
        switch (product) {
          case 'course':
          case 'classroom':
            targetType = '部分商品';
            break;
          default:
            targetType = '';
        }
      }

      if (type === 'minus') {
        discountType = '抵价';
        text = '元';
      }

      return `${discountType} ${rate} ${text} / ${targetType}`;
    }
  },
  {
    title: '剩余/总量',
    width: '15%',
    customRender: function(record) {
      const { unreceivedNum, generatedNum } = record;
      return `${unreceivedNum} / ${generatedNum}`;
    }
  },
  {
    title: '有效期至',
    width: '20%',
    customRender: function(record) {
      const { deadlineMode, deadline, fixedDay } = record;

      if (deadlineMode === 'day') {
        return `领取${fixedDay}天内有效`;
      }

      if (deadlineMode === 'time') {
        return moment(deadline).format('YYYY-MM-DD');
      }

      return '未知日期';
    }
  }
];

export default {
  name: 'CourseLinkModal',

  props: {
    coupon: {
      type: Array,
      required: true
    }
  },

  data() {
    return {
      visible: false,
      data: [],
      keyword: '',
      loading: false,
      columns,
      selectedRowKeys: [],
      selectedCoupon: [],
      pagination: {
        pageSize: 10,
        current: 1,
        hideOnSinglePage: true
      }
    }
  },

  methods: {
    showModal() {
      _.assign(this, {
        visible: true,
        keyword: '',
        selectedCoupon: [],
        selectedRowKeys: []
      });

      this.pagination.current = 1;
      this.initSelectedRowKeys();
      this.fetch();
    },

    initSelectedRowKeys() {
      const tempArr = [];
      _.forEach(this.coupon, item => {
        tempArr.push(item.id);
      });
      this.selectedRowKeys = tempArr;
    },

    handleCancel() {
      this.visible = false;
    },

    onSearch() {
      this.pagination.current = 1;
      this.fetch();
    },

    onSelectChange(selectedRowKeys) {
      this.selectedRowKeys = selectedRowKeys;
    },

    onSelect(record, selected, selectedRows) {
      this.selectedCoupon = selectedRows;
    },

    onSelectAll(selected, selectedRows) {
      this.selectedCoupon = selectedRows;
    },

    handleOk() {
      this.$emit('select-coupon', this.selectedCoupon);
      this.visible = false;
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
        params: {
          limit: pageSize,
          offset: pageSize * (current - 1),
          unexpired: 1,
          unreceivedNumGt: 0,
          name: this.keyword
        }
      };

      const { data, paging: { total } } = await Coupon.get(params);
      const pagination = { ...this.pagination };
      pagination.total = total;

      _.assign(this, {
        loading: false,
        data,
        pagination
      });
    }
  }
};
</script>

<style lang="less" scoped>
.modal-title-tips {
  margin-left: 10px;
  font-size: 12px;
  color: #919191;
}
</style>
