import Vue from 'vue';
import store from '@/store';
import * as types from '@/store/mutation-types';
import Router from 'vue-router';
import find from './find';
import learning from './learning';
import my from './my';

Vue.use(Router);
// 路由懒加载 实现代码分离
const routes = [
  {
    path: '/',
    meta: {
      title: '', // navbar 显示的title
      index: 0, // 转场动画决定前进后退的动画
      keepAlive: true // tabbar 的三个页面需要缓存下来，减少首页白屏加载次数
    },
    component: () => import(/* webpackChunkName: "home" */ '@/containers/home.vue'),
    children: [
      ...find,
      ...learning,
      ...my,
      {
        path: '/prelogin',
        name: 'prelogin',
        meta: {
          title: '我的',
          index: 1
        },
        component: () =>
          import(/* webpackChunkName: "loginPrelogin" */'@/containers/login/prelogin.vue')
      }
    ]
  }, {
    path: '/login',
    name: 'login',
    meta: {
      title: '',
      index: 10
    },
    component: () => import(/* webpackChunkName: "login" */ '@/containers/login/index.vue')
  }, {
    path: '/login/qrcode',
    name: 'login_qrcode',
    meta: {
      title: '',
      index: 30
    },
    component: () => import(/* webpackChunkName: "login" */ '@/containers/login/face/index.vue')
  }, {
    path: '/register',
    name: 'register',
    meta: {
      title: '',
      index: 30
    },
    component: () => import(/* webpackChunkName: "register" */'@/containers/register/index.vue')
  }, {
    path: '/protocol',
    name: 'protocol',
    meta: {
      title: ''
    },
    component: () => import(/* webpackChunkName: "protocol" */'@/containers/register/protocol/index.vue')
  }, {
    path: '/settings',
    name: 'my_setting',
    meta: {
      title: '设置',
      index: 10
    },
    component: () => import(/* webpackChunkName: "setting" */'@/containers/my/setting/index.vue')
  }, {
    path: '/setting/nickname',
    name: 'setting_nickname',
    meta: {
      title: '昵称设置',
      index: 20
    },
    component: () => import(/* webpackChunkName: "nickname" */'@/containers/my/setting/nickname.vue')
  }, {
    path: '/course/try',
    name: 'course_try',
    meta: {
      index: 30
    },
    component: () => import(/* webpackChunkName: "courseTry" */'@/containers/course/try.vue')
  }, {
    path: '/course/web',
    name: 'course_web',
    meta: {
      index: 30
    },
    component: () => import(/* webpackChunkName: "webView" */'@/containers/course/detail/web-view.vue')
  }, {
    path: '/course/audioview',
    name: 'course_audioview',
    meta: {
      index: 30
    },
    component: () => import(/* webpackChunkName: "audioDoc" */ '@/containers/course/detail/audio-doc.vue')
  }, {
    path: '/live',
    name: 'live',
    meta: {
      index: 30
    },
    component: () => import(/* webpackChunkName: "live" */'@/containers/course/detail/live-view.vue')
  }, {
    path: '/course/explore',
    name: 'more_course',
    meta: {
      title: '所有课程',
      index: 10
    },
    component: () => import(/* webpackChunkName: "more" */'@/containers/more/course/index.vue')
  }, {
    path: '/classroom/explore',
    name: 'more_class',
    meta: {
      title: '所有班级',
      index: 10
    },
    component: () => import(/* webpackChunkName: "more" */'@/containers/more/classroom/index.vue')
  }, {
    path: '/course/:id',
    name: 'course',
    meta: {
      title: '课程详情',
      index: 20
    },
    component: () => import(/* webpackChunkName: "course" */'@/containers/course/index.vue')
  }, {
    path: '/classroom/:id',
    name: 'classroom',
    meta: {
      title: '班级详情',
      index: 20
    },
    component: () => import(/* webpackChunkName: "classroom" */'@/containers/classroom/index.vue')
  }, {
    path: '/comment/:id',
    name: 'comment',
    meta: {
      title: '学员评价',
      index: 30
    },
    component: () => import(/* webpackChunkName: "comment" */'@/containers/comment/index.vue')
  }, {
    path: '/order/:id',
    name: 'order',
    meta: {
      title: '确认订单',
      index: 40
    },
    component: () => import(/* webpackChunkName: "order" */'@/containers/order/index.vue')
  }, {
    path: '/pay',
    name: 'pay',
    meta: {
      title: '订单支付',
      index: 50
    },
    component: () => import(/* webpackChunkName: "pay" */'@/containers/pay/index.vue')
  }, {
    path: '/weixin_pay',
    name: 'wxpay',
    meta: {
      title: '微信支付',
      index: 50
    },
    component: () => import(/* webpackChunkName: "wxpay" */'@/containers/wxpay/index.vue')
  }, {
    path: '/preview',
    name: 'preview',
    meta: {
      title: '预览'
    },
    component: () => import(/* webpackChunkName: "preview" */'@/containers/preview/index.vue')
  }, {
    path: '/sts',
    name: 'sts',
    meta: {
      title: '人脸识别登录',
      index: 10
    },
    component: () => import(/* webpackChunkName: "login" */ '@/containers/login/face/index.vue')
  }, {
    path: '/face_verification',
    name: 'verification',
    meta: {
      title: '人脸认证',
      index: 20
    },
    component: () => import(/* webpackChunkName: "verification" */'@/containers/login/face/verification.vue')
  }, {
    path: '/coupon/:token/receive',
    name: 'coupon_receive',
    meta: {
      title: '优惠券领取',
      index: 10
    },
    component: () => import(/* webpackChunkName: "coupon_receive" */'@/containers/coupon/index.vue')
  }
];

// 页面刷新，store数据会被清掉，需对token、user重新赋值
if (localStorage.getItem('token')) {
  store.commit(types.USER_LOGIN, {
    token: localStorage.getItem('token'),
    user: JSON.parse(localStorage.getItem('user'))
  });
}

const router = new Router({
  routes
});

router.beforeEach((to, from, next) => {
  const shouldUpdateMetaTitle = ['register', 'login', 'protocol', 'find'].includes(to.name);

  // 转场动画 meta.index 决定路由层级
  if (to.meta.index > from.meta.index) {
    store.state.routerTransition = 'slide-left';
  } else {
    store.state.routerTransition = 'slide-right';
  }

  if (!Object.keys(store.state.courseSettings).length) {
    store.dispatch('getGlobalSettings', {
      type: 'course',
      key: 'courseSettings'
    });
  }

  if (!Object.keys(store.state.settings).length) {
    // 获取全局设置
    store.dispatch('getGlobalSettings', {
      type: 'site',
      key: 'settings'
    }).then(res => {
      if (shouldUpdateMetaTitle) {
        to.meta.title = res.name;
      }
      next();
    });
  } else if (shouldUpdateMetaTitle) {
    to.meta.title = store.state.settings.name;
    next();
  } else {
    next();
  }
});
export default router;
