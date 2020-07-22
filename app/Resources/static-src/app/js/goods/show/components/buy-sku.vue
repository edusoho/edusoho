<template>
    <div>
        <a v-if="sku" :class="btnClass" href="javascript:;" @click="buySku">
            <slot>立即购买</slot>
        </a>
    </div>
</template>

<script>
    import axios from 'axios';
    import qs from 'qs';

    axios.interceptors.request.use((config) => {
        config.headers = {
            'Accept': 'application/vnd.edusoho.v2+json',
            'X-CSRF-Token': $('meta[name=csrf-token]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        };

        return config;
    });

    export default {
        name: "buy-sku",
        props: {
            btnClass: {
                type: String,
                default: null,
            },
            sku: {
                type: Object,
                default: null
            },
            isUserLogin: {
                type: Number,
                default: 0,
            }
        },
        methods: {
            renderModal(template) {
                axios.get('/goods/buy_flow/modal?' + qs.stringify({template: template}), {}).then(res => {
                    $('#modal').modal('show').html(res.data);
                });
            },
            buySku() {
                if (!this.isUserLogin) {
                    axios.get($('#login-modal').data('url')).then(res => {
                        $('#login-modal').modal('show').html(res.data);
                    });
                    return;
                }
                ;

                axios({
                    url: '/api/goods/' + this.sku.goodsId + '/buy',
                    method: "POST",
                    data: {
                        'targetId': this.sku.id,
                    }
                }).then(res => {
                    console.log(res.data);
                    if (res.data.success) {
                        window.location.href = res.data.url;
                        return;
                    }

                    if (res.data.url) {
                        window.location.href = res.data.url;
                        return;
                    }

                    if (res.data.noticeTemplate) {
                        this.renderModal(res.data.noticeTemplate);
                    }
                });
            }
        }
    }
</script>

<style scoped>

</style>