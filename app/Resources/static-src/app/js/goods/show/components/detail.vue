<template>
  <div class="product-detail clearfix">
      
    <div class="product-detail__left detail-left pull-left">
      <img :src="detailData.image" alt="">
      <ul class="detail-left__text clearfix">
        <li class="pull-left"><i class="es-icon es-icon-friends"></i>{{ currentPlan.joinedNum }}人加入学习</li>
        <li class="pull-right">
          <span class="detail-left__text-share" style="cursor: pointer;"><i class="es-icon es-icon-share"></i>分享</span>
          <template v-if="isFavorite">
            <span @click="onFavorite" style="color: #FF7E56;" class="detail-hover-span">
              <i class="es-icon es-icon-favorite" style="color: #FF7E56;"></i>已收藏
            </span>
          </template>
          <template v-else>
            <span @click="onFavorite" class="detail-hover-span">
              <i class="es-icon es-icon-favoriteoutline"></i>收藏
            </span>
          </template>
        </li>
      </ul>
    </div>

    <div class="product-detail__right detail-right pull-right">
      <p class="detail-right__title">{{ detailData.title }}</p>
      <p class="detail-right__subtitle">{{ detailData.subtitle }}</p>

      <!-- 价格 -->
      <div class="detail-right__price">
        <!-- 优惠活动 -->
        <div class="detail-right__price__activities">该商品享受“某某某某某某某某某某某某”打折促销活动，24：00：00后结束，请尽快购买！</div>
        <div class="detail-right__price__num">
          优惠价
          <span>{{ currentPlan.price }}</span>
          <s>价格: 2000元</s>
        </div>
      </div>
      
      <!-- 教学计划 -->
      <div class="detail-right__plan plan clearfix" v-if="detailData.specs">
        <div class="plan-title pull-left">教学计划</div>
        <div class="plan-btns pull-right">
          <span class="plan-btns__item" v-for="plan in detailData.specs" :key="plan.id" :class="{ active: plan.active }" @click="handleClick(plan)">{{ plan.title }}</span>
        </div>
      </div>

      <!-- 学习有效期 -->
      <div class="detail-right__validity validity clearfix">
        <span class="validity-title pull-left">学习有效期</span>
        <span class="validity-content pull-left">{{ currentPlan.expiryMode }}</span>
      </div>

      <!-- 承诺服务 -->
      <div class="detail-right__promise promise clearfix" v-if="currentPlan.services">
        <div class="promise-title pull-left">承诺服务</div>
        <div class="promise-content pull-left">
          <span class="promise-content__item" v-for="(item, index) in currentPlan.services" :key="index">疑</span>
        </div>
      </div>

      <!-- 立即购买 -->
      <div class="product-detail__btn">立即购买</div>
    </div>
  </div>
</template>

<script>
  import axios from 'axios';
  export default {
    data() {
      return {
        isFavorite: false
      }
    },
    props: {
      detailData: {
        type: Object,
        default: () => {}
      },
      currentPlan: {
        type: Object,
        default: () => {}
      }
    },
    methods: {
      // 添加收藏
      addFavorite(id) {
        axios({
          url: "/api/favorite",
          method: "POST",
          data: {
            'targetType': 'goods',
            'targetId': id
          },
          headers: {
            'Accept': 'application/vnd.edusoho.v2+json',
            'X-CSRF-Token': $('meta[name=csrf-token]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
          }
        }).then(res => {
          this.isFavorite = true;
        });
      },
      // 移除收藏
      removeFavorite(id) {
        axios({
          url: "/api/favorite",
          method: "DELETE",
          params: {
            'targetType': 'goods',
            'targetId': id
          },
          headers: {
            'Accept': 'application/vnd.edusoho.v2+json',
            'X-CSRF-Token': $('meta[name=csrf-token]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
          }
        }).then(res => {
          this.isFavorite = false;
        });
      },
      // 收藏/移除收藏
      onFavorite() {
        if (this.isFavorite) {
          this.removeFavorite(this.currentPlan.id);
        } else {
          this.addFavorite(this.currentPlan.id);
        }
      },
      handleClick(plan) {
        this.$emit('changePlan', plan.id);
      }
    }
  }
</script>
