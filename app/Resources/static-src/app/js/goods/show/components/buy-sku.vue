<template>
    <div>
        <a v-if="sku" :class="btnClass" href="javascript:;" @click="buySku">
            <slot>立即购买</slot>
        </a>
    </div>
</template>

<script>
    import axios from 'axios';

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
        },
        methods: {
            buySku() {
                axios({
                    url: '/api/goods/' + this.sku.id + '/buy',
                    method: "POST",
                }).then(res => {
                    if (typeof res.data === 'object') {
                        window.location.href = res.data.url;
                    } else {
                        $('#modal').modal('show').html(res.data);
                    }

                    if (res.data.error) {
                        return;
                    }
                });
            }
        }
    }
</script>

<style scoped>

</style>