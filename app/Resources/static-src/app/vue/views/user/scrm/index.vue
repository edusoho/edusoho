<template>
  <div class="aside-container">
    <div class="aside-header">
      <div class="aside-header__title">销客助手</div>
    </div>

    <div class="scrm-sec">
      <div class="scrm-sec__title">EduSoho销客助手</div>
      <div class="scrm-sec__img">
        <img src="/static-dist/app/img/vue/scrm-right.png" alt="">
      </div>
      <div class="scrm-sec__content">EduSoho销客助手是一个面向教培机构的私域流量运营工具，提供了引流获客、客户运营、社群运营、企业风控等功能。EduSoho销客助手与EduSoho网校一起为机构提供了招生、教务、教学和服务的整体解决方案。</div>
      <div class="scrm-sec__btn">
        <a-button class="mr16" @click="handleClickGoScrm">前往销客助手</a-button>
        <a-button type="primary" @click="showModal">立即开通</a-button>
      </div>
    </div>

    <div class="scrm-sec">
      <div class="scrm-sec__title">产品特性</div>
      <div class="scrm-characteristic">
        <div class="characteristic-box">
          <div
            v-for="(item, index) in characteristic1"
            :key="index"
            class="scrm-characteristic__item"
          >
            <div class="characteristic-img">
              <img :src="item.img" :alt="item.title">
            </div>
            <div class="characteristic-title">{{ item.title }}</div>
            <div class="characteristic-desc">{{ item.desc }}</div>
          </div>
        </div>

        <div class="characteristic-box" style="margin-top: 60px;">
          <div
            v-for="(item, index) in characteristic2"
            :key="index"
            class="scrm-characteristic__item"
          >
            <div class="characteristic-img">
              <img :src="item.img" :alt="item.title">
            </div>
            <div class="characteristic-title">{{ item.title }}</div>
            <div class="characteristic-desc">{{ item.desc }}</div>
          </div>
        </div>
      </div>
    </div>

    <a-modal
      title="开通EduSoho销客助手"
      :visible="visible"
      :footer="null"
      :width="584"
      @cancel="handleCancel"
    >
      <div class="scrm-modal">
        <div class="scrm-modal__title">已有阔知专属商务经理或客服</div>
        <div class="scrm-modal__desc">请联系您的商务经理或客服开通</div>
        <div class="scrm-modal__title" style="margin-top: 36px;">没有阔知专属商务经理或客服</div>
        <div class="scrm-modal__desc">请扫下面二维码添加专属商务经理为您服务</div>
        <div class="scrm-modal__img">
          <img src="/static-dist/app/img/vue/scrm-qr.png" alt="">
        </div>
        <div class="scrm-modal__tips">
          注：开通EduSoho前须先开通企业微信
          <a class="ml8" href="https://work.weixin.qq.com" target="_blank">去开通企业微信</a>
        </div>
      </div>
    </a-modal>
  </div>
</template>

<script>
const characteristic1 = [
  {
    img: '/static-dist/app/img/vue/scrm1.png',
    title: '引流获客，高效构建客户池',
    desc: '基于企业微信的好友和社群特性，为教培机构提供灵活的获客工具，帮助培训机构高效构建客户池。'
  },
  {
    img: '/static-dist/app/img/vue/scrm2.png',
    title: '线上成交，真正做到无缝对接',
    desc: '精细化营销管理，提升转化效率；客户通过线上成交后，可自动开通教学平台账号实施培训，真正做到无缝对接。'
  },
  {
    img: '/static-dist/app/img/vue/scrm3.png',
    title: '快速客户跟进，提升效率',
    desc: '发送资料、标注属性功能提升客户对产品、员工对客户的了解，快捷回复、群发消息、标准话术库等提升客户跟进的效率。'
  }
];

const characteristic2 = [
  {
    img: '/static-dist/app/img/vue/scrm4.png',
    title: '客户管理，实现客户资源企业化',
    desc: '通过后台查看企业客户整体信息，包括客户基本信息、好友关系、群聊信息、客户跟进状态等，实现客户资源的企业化。'
  },
  {
    img: '/static-dist/app/img/vue/scrm5.png',
    title: '销售分析，一目了然业绩情况',
    desc: '对企业营销的整体情况进行分析；当销售完成客户跟进后，管理员还可对销售的销售效果进行分析。'
  },
  {
    img: '/static-dist/app/img/vue/scrm6.png',
    title: '直播公开课',
    desc: '机构快速开设直播公开课，学员可在直播间购买正课；学员可邀请好友到直播间学习，实现裂变。'
  }
];
export default {
  data() {
    return {
      characteristic1,
      characteristic2,
      visible: false
    }
  },

  methods: {
    handleClickGoScrm() {
      window.open('https://scrm.edusoho.com');
    },

    handleCancel() {
      this.visible = false;
    },

    showModal() {
      this.visible = true;
    }
  }
}
</script>

<style lang="less" scoped>
.aside-container {
  margin-top: -10px;
  padding: 16px;

  .aside-header {
    &__title {
      position: relative;
      padding-left: 22px;
      font-size: 16px;
      color: #333;
      letter-spacing: 0;
      line-height: 26px;

      &::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 8px;
        transform: translateY(-50%);
        width: 2px;
        height: 14px;
        background: #1890ff;
        border-radius: 1px;
      }
    }
  }

  .scrm-sec {
    position: relative;
    padding: 24px;
    margin-top: 16px;
    background-color: #fff;
    border-radius: 2px;
    font-size: 14px;
    color: #333;
    letter-spacing: 0;

    &__title {
      font-weight: 500;
      line-height: 26px;
    }

    &__img {
      position: absolute;
      top: 7px;
      height: 200px;
      right: 120px;

      img {
        width: auto;
        height: 100%;
      }
    }

    &__content {
      margin-top: 20px;
      margin-left: 72px;
      margin-right: 480px;
      line-height: 26px;
    }

    &__btn {
      margin: 24px 0 8px 72px;
    }

    @media (max-width: 1500px) {
      &__content {
        margin-left: 0;
        margin-right: 338px;
      }

      &__img {
        right: 97px;
      }
    }

    .scrm-characteristic {
      padding: 50px 72px 26px;

      @media (max-width: 1500px) {
        padding: 50px 0 26px;
      }

      .characteristic-box {
        position: relative;
        padding: 0 320px;
        height: 315px;
      }

      &__item {
        position: absolute;
        top: 0;
        width: 320px;
        height: 315px;

        .characteristic-img {
          width: 100%;
          height: 206px;

          img {
            width: 100%;
            height: 100%;
          }
        }

        .characteristic-title {
          margin-top: 16px;
          font-weight: 500;
          line-height: 18px;
          text-align: center;
        }

        .characteristic-desc {
          margin-top: 8px;
          color: #666;
          line-height: 22px;
        }
      }

      &__item:nth-child(1) {
        left: 0;
      }

      &__item:nth-child(2) {
        left: 50%;
        transform: translateX(-50%);
      }

      &__item:nth-child(3) {
        right: 0;
      }
    }
  }
}

.scrm-modal {
  padding-left: 128px;

  &__title {
    position: relative;
    font-weight: 500;
    font-size: 18px;
    color: #333;
    line-height: 24px;

    &::before {
      content: "";
      position: absolute;
      top: 50%;
      left: -16px;
      transform: translateY(-50%);
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #1890ff;
    }
  }

  &__desc {
    margin-top: 8px;
    font-size: 16px;
    color: #666;
    line-height: 22px;
  }

  &__img {
    margin-top: 18px;
    width: 246px;
    height: 246px;

    img {
      width: 100%;
      height: 100%;
    }
  }

  &__tips {
    margin-top: 18px;
    font-size: 12px;
    color: #999;
    line-height: 16px;

    a {
      font-size: 12px;
    }
  }
}
</style>
