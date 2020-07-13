<template>
    <div class="es-share top js-es-share">
        <span class="dropdown-toggle" :class="customizedClass" data-toggle="dropdown">
            <i class="es-icon es-icon-share"></i>
             <slot></slot>
        </span>

        <div class="dropdown-menu js-social-share-params">
            <a href="javascript:;" class="js-social-share" data-cmd="weixin" @click="onWeChatShare">
                <i class="es-icon es-icon-weixin"></i></a>
            <a href="javascript:;" class="js-social-share" @click="onWeiboShare">
                <i class="es-icon es-icon-weibo"></i></a>
            <a href="javascript:;" class="js-social-share" @click="onQQShare">
                <i class="es-icon es-icon-qq"></i></a>
            <a href="javascript:;" class="js-social-share" @click="onQzoneShare">
                <i class="es-icon es-icon-qzone"></i></a>
        </div>

        <div class="modal fade weixin-share-modal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
                        </button>
                        <h4 class="modal-title">{{ 'share.share_to_wechat_circle_of_friends_hint'|trans }}</h4>
                    </div>
                    <div class="modal-body">
                        <p class="weixin-share-qrcode text-center">
                            <img :src="qrCode">
                        </p>
                        <p class="text-muted text-center">
                            <small v-html="wechatShareUsageHint"></small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import qs from 'qs';

    export default {
        name: "share",
        props: {
            title: {
                type: String,
                default: 'title',
            },
            summary: {
                type: String,
                default: 'summary',
            },
            message: {
                type: String,
                default: 'message',
            },
            url: {
                type: String,
                default: 'http://t5.edusoho.cn',
            },
            picture: {
                type: String,
                default: 'http://sce2a3b1c3d5nk-sb-qn.qiqiuyun.net/files/default/2019/08-09/18334266a615121661.jpg',
            },
            customizedClass: {
                type: Object,
                default: null
            }

        },
        data() {
            return {
                qrCode: '/common/qrcode?' + qs.stringify({text: this.url}),
                wechatShareUsageHint: Translator.trans('share.wechat_share_usage_hint')
            };
        },
        methods: {
            onWeChatShare() {
                console.log('weixin');
                $('.weixin-share-modal').modal('show');
            },
            onWeiboShare() {
                console.log('weibo');
                let query = {
                    url: this.url,
                    title: this.message,
                };

                if (this.picture != '') {
                    if (this.picture.indexOf('://') != -1) {
                        query.pic = this.picture;
                    } else {
                        query.pic = document.domain + this.picture;
                    }
                }

                window.open('http://service.weibo.com/share/share.php?' + qs.stringify(query));
            },
            onQQShare() {
                console.log('qq');
                let query = {
                    url: this.url,
                    title: this.title,
                    summary: this.summary,
                    desc: this.message,
                };
                if (this.picture != '') {
                    query.pics = this.picture;
                }

                window.open('http://connect.qq.com/widget/shareqq/index.html?' + qs.stringify(query));
            },
            onQzoneShare() {
                console.log('qzone');
                let query = {
                    url: this.url,
                    title: this.title,
                    summary: this.summary,
                    desc: this.message,
                };

                if (this.picture != '') {
                    query.pics = this.picture;
                }

                window.open('http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?' + qs.stringify(query));
            },
        },
        filters: {
            trans(value, params) {
                if (!value) return value;
                return Translator.trans(value, params);
            }
        }
    }
</script>

<style scoped>

</style>