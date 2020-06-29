<template>
  <div class="detail-info">
    <p class="detail-info__title text-overflow">{{ details.courseSetTitle }}</p>
    <div class="detail-info__price">
      <div class="clearfix">
        <div class="pull-left">
          优惠价
          <span class="price">129.00</span>
          元
        </div>
        <div class="pull-right study-num">{{ details.studentNum }}人在学</div>
      </div>
      <p class="original-price"><s>价格&nbsp;<span>399.00</span>元</s></p>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';
export default {
  computed: {
    ...mapState("course", {
      details: state => state.details
    })
  }
}
</script>