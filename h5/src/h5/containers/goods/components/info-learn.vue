<template>
  <div class="info-learn">
    <div class="info-learn__header clearfix">
      <slot class="header-title pull-left" name="title"></slot>
      <span class="header-more pull-right">更多<i class="iconfont icon-About"></i></span>
    </div>
    <div class="info-learn__body">
      <div class="body-item" v-for="count in 10" :key="count">
        <div class="body-item__img">
          <img src="http://devtest.edusoho.cn/files/system/2019/08-07/1747506b1910625434.JPG" alt="">
          <div class="learners-mask"></div>
          <span class="learners-number"><i class="iconfont icon-renqi"></i>8888</span>
        </div>
        <div class="body-item__content">
          <p class="content-title text-overflow">Edusoho经典案例案…Edusoho经典案例案…</p>
          <p class="content-price text-overflow">12000.0 Edusoho学习币…</p>
        </div>
      </div>
    </div>
  </div>
</template>