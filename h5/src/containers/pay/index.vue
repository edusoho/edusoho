<template>
  <div class="payPage">
    <div class="payPage__order">
      <div class="order__head">
        支付方式
      </div>
      <div class="order__infomation">
        <div class="title">Photoshop基础入门课程</div>
        <div class="sum">
          <span>待支付</span>
          <span class="sum__price">¥ <span class="num">99</span></span>
        </div>
        <div class="payWay">
          <div class="payWay__item payWay__item--selected">
            <div class="right"></div>
            <i></i>
            <img src="static/images/zfb.png">
          </div>
          <div class="payWay__item">
            <div class="right"></div>
            <i></i>
            <img src="static/images/wx.png">
          </div>
        </div>
      </div>
    </div>
    <div class="payPage__payBtn">
      立即支付
    </div>
  </div>
</template>

<script>
export default {
  data () {
    return {

    };
  },
}
</script>
