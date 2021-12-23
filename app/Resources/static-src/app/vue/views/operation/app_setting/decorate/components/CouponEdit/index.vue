<template>
  <edit-layout>
    <template #title>{{ 'decorate.coupon' | trans }}</template>
    <template #subtitle>（{{ 'decorate.only_show_coupons_no_expired' | trans }}）</template>

    <div class="design-editor">
      <div class="design-editor__item">
        <span class="design-editor__label">{{ 'decorate.title' | trans }}：</span>
        <a-radio-group :default-value="moduleData.titleShow" @change="changeShowTitle">
          <a-radio value="show">
            {{ 'decorate.show' | trans }}
          </a-radio>
          <a-radio value="unshow">
            {{ 'decorate.do_not_show' | trans }}
          </a-radio>
        </a-radio-group>
      </div>

      <div class="design-editor__item">
        <span class="design-editor__label design-editor__required">{{ 'decorate.coupon_selection' | trans }}：</span>
        <a-button @click="handleSelectCoupon">{{ 'decorate.add_coupon' | trans }}</a-button>
      </div>

      <div class="design-editor__item">
        <draggable
          class="coupon-list"
          v-model="moduleData.items"
          v-bind="dragOptions"
          @start="drag = true"
          @end="drag = false"
        >
          <transition-group type="transition" :name="!drag ? 'flip-list' : null">
            <div class="coupon-list__item" v-for="item in moduleData.items" :key="item.id">
              <a-icon type="drag" style="color: #999;" /> {{ item.name }}
            </div>
          </transition-group>
        </draggable>
      </div>
    </div>

    <select-coupon-modal :coupon="moduleData.items" ref="modal" @select-coupon="selectConpon" />
  </edit-layout>
</template>

<script>
import _ from 'lodash';
import EditLayout from '../EditLayout.vue';
import Draggable from 'vuedraggable';
import SelectCouponModal from './SelectCouponModal.vue';

export default {
  name: 'CouponEdit',

  props: {
    moduleData: {
      type: Object,
      required: true
    }
  },

  components: {
    EditLayout,
    SelectCouponModal,
    Draggable
  },

  data() {
    return {
      drag: false
    }
  },

  computed: {
    dragOptions() {
      return {
        animation: 200,
        group: "description",
        disabled: false,
        ghostClass: "ghost"
      }
    }
  },

  methods: {
    handleSelectCoupon() {
      this.$refs.modal.showModal();
    },

    selectConpon(params) {
      this.$emit('update-edit', {
        type: 'coupon',
        key: 'items',
        value: [...params]
      });
    },

    changeShowTitle(e) {
      this.$emit('update-edit', {
        type: 'coupon',
        key: 'titleShow',
        value: e.target.value
      });
    }
  }
}
</script>

<style lang="less" scoped>
.coupon-list {
  padding-right: 8px;
  padding-left: 8px;
  background: rgba(237, 237, 237, 0.53);

  &__item {
    padding: 8px 0;
    cursor: move;
    border-bottom: 1px solid #eee;

    &:last-child {
      border-bottom: none;
    }
  }
}
</style>
