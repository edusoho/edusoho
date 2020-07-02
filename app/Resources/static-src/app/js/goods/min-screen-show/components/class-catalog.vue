<template>
  <div class="class-catalog">
    <div class="class-catalog__item clearfix" v-for="count in 5" :key="count">
      <div class="item-img pull-left">
        <img src="http://devtest.edusoho.cn/files/system/2019/08-07/1747506b1910625434.JPG" alt="">
      </div>
      <div class="item-info pull-left">
        <p class="item-info__title text-overflow">超人的思维大脑超人的思维大脑超人的思维大脑</p>
        <p class="item-info__price">￥580.00</p>
        <p class="item-info__plan clearfix">
          <span class="pull-left">默认教学计划</span>
          <span class="pull-right">共12课时</span>
        </p>
      </div>
    </div>
  </div>
</template>