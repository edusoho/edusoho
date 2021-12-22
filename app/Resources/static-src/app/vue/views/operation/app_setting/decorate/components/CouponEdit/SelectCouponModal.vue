<template>
  <a-modal
    :visible="visible"
    :width="900"
    @ok="handleOk"
    @cancel="handleCancel"
  >
    <template #title>
      {{ 'decorate.choose_a_coupon' | trans }}
      <span class="modal-title-tips">{{ 'decorate.only_show_coupons_no_expired' | trans }}</span>
    </template>

    <div>
      <a-input-search
        v-model="keyword"
        :placeholder="'decorate.search_for_coupons' | trans"
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
    title: Translator.trans('decorate.coupon_name'),
    dataIndex: 'name',
    width: '20%'
  },
  {
    title: Translator.trans('decorate.prefix'),
    dataIndex: 'prefix',
    width: '15%'
  },
  {
    title: Translator.trans('decorate.offer_content'),
    width: '30%',
    customRender: function(record) {
      const { type, rate, targetDetail: { numType, product } } = record;

      let discountType = Translator.trans('decorate.discount');
      let text = Translator.trans('fold');
      let targetType = Translator.trans('all_products');

      if (numType === 'single') {
        switch (product) {
          case 'course':
          case 'classroom':
            targetType = Translator.trans('designated_goods');
            break;
          case 'vip':
            targetType = Translator.trans('designated_member');
            break;
          default:
            targetType = '';
        }
      } else if (numType === 'all') {
        switch (product) {
          case 'course':
            targetType = Translator.trans('all_courses');
            break;
          case 'classroom':
            targetType = Translator.trans('all_classes');
            break;
          case 'all':
            targetType = Translator.trans('all_products');
            break;
          case 'vip':
            targetType = Translator.trans('all_members');
            break;
          default:
            targetType = '';
        }
      } else {
        switch (product) {
          case 'course':
          case 'classroom':
            targetType = Translator.trans('some_products');
            break;
          default:
            targetType = '';
        }
      }

      if (type === 'minus') {
        discountType = Translator.trans('decorate.trade_in');
        text = Translator.trans('cny');
      }

      return `${discountType} ${rate} ${text} / ${targetType}`;
    }
  },
  {
    title: Translator.trans('decorate.remaining_total'),
    width: '15%',
    customRender: function(record) {
      const { unreceivedNum, generatedNum } = record;
      return `${unreceivedNum} / ${generatedNum}`;
    }
  },
  {
    title: Translator.trans('decorate.valid_until'),
    width: '20%',
    customRender: function(record) {
      const { deadlineMode, deadline, fixedDay } = record;

      if (deadlineMode === 'day') {
        return Translator.trans('decorate.valid_within_fixed_day_of_receipt', { fixedDay: fixedDay });
      }

      if (deadlineMode === 'time') {
        return moment(deadline).format('YYYY-MM-DD');
      }

      return Translator.trans('decorate.unknown_date');
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
