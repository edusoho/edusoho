<template>
    <span v-if="isFavorite" @click="onFavorite" style="color: #FF7E56;" class="detail-hover-span">
      <i class="es-icon es-icon-favorite mrs" style="color: #FF7E56;"></i>{{ 'site.favorited'|trans }}
    </span>
    <span v-else @click="onFavorite" class="detail-hover-span">
      <i class="es-icon es-icon-favoriteoutline mrs"></i>{{ 'site.favorite'|trans }}
    </span>
</template>

<script>
    import axios from "axios";
    import Api from 'common/api';

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
                Api.favorite.favorite({
                    data: {
                        'targetType': targetType,
                        'targetId': targetId,
                    }
                }).then((res) => {
                    this.isFavorite = true;
                });
            },

            removeFavorite(targetType, targetId) {
                Api.favorite.unfavorite({
                    data: {
                        'targetType': targetType,
                        'targetId': targetId,
                    }
                }).then((res) => {
                    this.isFavorite = false;
                });
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