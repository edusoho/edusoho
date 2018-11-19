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
      title: ''
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
          title: '我的'
        },
        component: () =>
          import(/* webpackChunkName: "loginPrelogin" */'@/containers/login/prelogin.vue')
      }
    ]
  }, {
    path: '/login',
    name: 'login',
    meta: {
      title: ''
    },
    component: () => import(/* webpackChunkName: "login" */ '@/containers/login/index.vue')
  }, {
    path: '/login/qrcode',
    name: 'login_qrcode',
    meta: {
      title: ''
    },
    component: () => import(/* webpackChunkName: "login" */ '@/containers/login/face/index.vue')
  }, {
    path: '/register',
    name: 'register',
    meta: {
      title: ''
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
      title: '设置'
    },
    component: () => import(/* webpackChunkName: "setting" */'@/containers/my/setting/index.vue')
  }, {
    path: '/setting/nickname',
    name: 'setting_nickname',
    meta: {
      title: '昵称设置'
    },
    component: () => import(/* webpackChunkName: "nickname" */'@/containers/my/setting/nickname.vue')
  }, {
    path: '/course/try',
    name: 'course_try',
    component: () => import(/* webpackChunkName: "courseTry" */'@/containers/course/try.vue')
  }, {
    path: '/course/web',
    name: 'course_web',
    component: () => import(/* webpackChunkName: "webView" */'@/containers/course/detail/web-view.vue')
  }, {
    path: '/course/audioview',
    name: 'course_audioview',
    component: () => import(/* webpackChunkName: "audioDoc" */ '@/containers/course/detail/audio-doc.vue')
  }, {
    path: '/live',
    name: 'live',
    component: () => import(/* webpackChunkName: "live" */'@/containers/course/detail/live-view.vue')
  }, {
    path: '/course/explore',
    name: 'more',
    meta: {
      title: ''
    },
    component: () => import(/* webpackChunkName: "more" */'@/containers/more/index.vue')
  }, {
    path: '/course/:id',
    name: 'course',
    meta: {
      title: '课程详情'
    },
    component: () => import(/* webpackChunkName: "course" */'@/containers/course/index.vue')
  }, {
    path: '/order/:id',
    name: 'order',
    meta: {
      title: '确认订单'
    },
    component: () => import(/* webpackChunkName: "order" */'@/containers/order/index.vue')
  }, {
    path: '/pay',
    name: 'pay',
    meta: {
      title: '订单支付'
    },
    component: () => import(/* webpackChunkName: "pay" */'@/containers/pay/index.vue')
  }, {
    path: '/weixin_pay',
    name: 'wxpay',
    meta: {
      title: '微信支付'
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
      title: '人脸识别登录'
    },
    component: () => import(/* webpackChunkName: "login" */ '@/containers/login/face/index.vue')
  }, {
    path: '/face_verification',
    name: 'verification',
    meta: {
      title: '人脸认证'
    },
    component: () => import(/* webpackChunkName: "verification" */'@/containers/login/face/verification.vue')
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
  console.error(4);
  if (!Object.keys(store.state.courseSettings).length) {
    store.dispatch('getGlobalSettings', {
      type: 'course',
      key: 'courseSettings'
    });
  }

  if (!Object.keys(store.state.settings).length) {
    // 获取全局设置
    console.error(23);
    store.dispatch('getGlobalSettings', {
      type: 'site',
      key: 'settings'
    }).then(res => {
      console.error(1);
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
