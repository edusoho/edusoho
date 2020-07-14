<template>
    <div  class="info-right-qr info-right-box">
        <div class="title">{{ mpQrcode.title }}</div>
        <div class="qr-info">
            <div class="qr-info__img">
                <img :src="mpQrcode.imageUrl" alt="">
            </div>
            <div class="qr-info__introduce">
                <p></p>
                <p>{{ mpQrcode.content }}</p>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            mpQrcode: {
                type: Object,
                default: function () {
                    return []
                }
            }
        }
    }
</script>