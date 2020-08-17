<template>
    <span v-if="isFavorite" @click="onFavorite" style="color: #FF7E56;" class="detail-hover-span">
      <i class="es-icon es-icon-favorite mrs" style="color: #FF7E56;"></i>已收藏
    </span>
    <span v-else @click="onFavorite" class="detail-hover-span">
      <i class="es-icon es-icon-favoriteoutline mrs"></i>收藏
    </span>
</template>

<script>
    import axios from "axios";

    export default {
        props: {
            isFavorite: {
                type: Boolean,
                required: true
            },

            targetType: {
                type: String,
                required: true
            },

            targetId: {
                type: Number,
                required: true

            },
        },
        methods: {
            addFavorite(targetType, targetId) {
                axios({
                    url: "/api/favorite",
                    method: "POST",
                    data: {
                        'targetType': targetType,
                        'targetId': targetId
                    },
                    headers: {
                        'Accept': 'application/vnd.edusoho.v2+json',
                        'X-CSRF-Token': $('meta[name=csrf-token]').attr('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(res => {
                    this.isFavorite = true;
                }).catch();
            },

            removeFavorite(targetType, targetId) {
                axios({
                    url: "/api/favorite",
                    method: "DELETE",
                    data: {
                        'targetType': targetType,
                        'targetId': targetId
                    },
                    headers: {
                        'Accept': 'application/vnd.edusoho.v2+json',
                        'X-CSRF-Token': $('meta[name=csrf-token]').attr('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(res => {
                    this.isFavorite = false;
                }).catch();
            },

            onFavorite() {
                if (this.isFavorite) {
                    this.removeFavorite(this.targetType, this.targetId);
                } else {
                    this.addFavorite(this.targetType, this.targetId);
                }
            },
        }
    }
</script>