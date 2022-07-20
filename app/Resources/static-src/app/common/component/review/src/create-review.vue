<template>
    <div class="create-review" v-if="canCreate">
        <form v-show="!userReview || (userReview && showForm)">
            <div class="review-form-rating create-review__grade">
                {{ 'validate.raty_star.message'|trans }}：
                <span @mouseleave="leaveRating">
                    <img v-for="(star, index) in stars" :src="star.src" :key="index" @click="rating(index)"
                         @mouseenter="enterRating(index)"/>
                </span>
                <p></p>
            </div>
            <div class="review-form-content t222">
                <textarea class="form-control" rows="5" v-model="form.content"></textarea>
                <p></p>
            </div>
            <div class="create-review__btn">
                <span class="btn-cancel" @click="onCancle(false)">{{ 'site.cancel'|trans }}</span>
                <span class="btn-confirm" @click="onConfirm">{{ 'form.btn.save'|trans }}</span>
            </div>
        </form>

        <div v-show="userReview && !showForm" class="create-review__btn" @click="onCancle(true)"><span
            class="btn-confirm">{{ 'reviews.review_again'|trans }}</span>
        </div>
    </div>
</template>

<script>
    import reviewModule from "../../../../../common/api/modules/review";
    import Captcha from 'app/common/captcha';

    let starOnImg = '/assets/img/raty/star-on.png';
    let starOffImg = '/assets/img/raty/star-off.png';
    import axios from 'axios';
    import Api from 'common/api';

    axios.interceptors.request.use((config) => {
        config.headers = {
            'Accept': 'application/vnd.edusoho.v2+json',
            'X-CSRF-Token': $('meta[name=csrf-token]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        };

        return config;
    });

    axios.interceptors.response.use((response) => {
        return response;
    }, (error) => {
        if (error.response.data.error.message) {
            cd.message({
                'type': 'danger',
                'message': error.response.data.error.message
            });
        } else {
            cd.message({
                'type': 'danger',
                'message': Translator.trans('site.service_error_hint')
            });
        }
        return error;
    });

    export default {
        created() {
            this.getUserReview();

            if (this.form.rating) {
                this.rating(this.form.rating - 1);
            }

            this.captcha.on('success', (data) => {
                if (data.type === 'create-review') {
                    this.captcha.isShowCaptcha = 0;
                    this._dragCaptchaToken = data.token;
                    this.onConfirm()
                }
            })
        },
        data() {
            return {
                stars: [{
                    src: starOffImg,
                    active: false
                }, {
                    src: starOffImg,
                    active: false
                }, {
                    src: starOffImg,
                    active: false
                }, {
                    src: starOffImg,
                    active: false
                }, {
                    src: starOffImg,
                    active: false
                }],
                userReview: null,
                form: {
                    targetType: this.targetType,
                    targetId: this.targetId,
                    rating: 0,
                    content: null,
                },
                starHover: 0,
                content: "",
                showForm: false,
                _dragCaptchaToken: ''
            }
        },
        filters: {
            trans(value, params) {
                if (!value) return '';
                return Translator.trans(value, params);
            },
        },
        props: {
            targetType: {
                type: String,
                default: null,
            },
            targetId: {
                type: [Number, String],
                default: null,
            },
            canCreate: {
                type: Boolean,
                default: false,
            },
            currentUserId: {
                type: Number,
                default: null
            },
            captcha: {
                type: Object,
                required: true
            }
        },
        methods: {
            getUserReview() {
                if (!this.currentUserId) {
                    return null;
                }

                axios.get('/api/reviews', {
                    params: {
                        targetType: this.targetType,
                        targetId: this.targetId,
                        userId: this.currentUserId,
                    },
                }).then(response => {
                    if (!response.data.paging.total) {
                        return;
                    }

                    this.userReview = response.data.data.shift();

                    this.form = {
                        targetType: this.userReview.targetType,
                        targetId: this.userReview.targetId,
                        rating: parseInt(this.userReview.rating),
                        content: this.removeHtml(this.userReview.content),
                    };

                    this.rating(this.form.rating - 1);
                });
            },
            enterRating(index) {
                let total = this.stars.length;
                let idx = index + 1;
                if (this.starHover == 0) {
                    this.starHover = idx;
                    for (let i = 0; i < this.stars.length; i++) {
                        if (i < this.starHover) {
                            this.stars[i].src = starOnImg;
                            this.stars[i].active = true;
                        } else {
                            this.stars[i].src = starOffImg;
                            this.stars[i].active = false;
                        }
                    }
                } else {
                    if (idx < this.starHover) {
                        for (let i = idx; i < this.starHover; i++) {
                            this.stars[i].src = starOffImg;
                            this.stars[i].active = false;
                        }
                    }
                    if (idx > this.starHover) {
                        for (let i = 0; i < idx; i++) {
                            this.stars[i].src = starOnImg;
                            this.stars[i].active = true;
                        }
                    }
                    let count = 0;
                    for (let i = 0; i < total; i++) {
                        if (this.stars[i].active) {
                            count++;
                        }
                    }
                    this.starHover = count;
                }

            },
            leaveRating() {
                for (let i = 0; i < this.stars.length; i++) {
                    if (i < this.form.rating) {
                        this.stars[i].src = starOnImg;
                        this.stars[i].active = true;
                    } else {
                        this.stars[i].src = starOffImg;
                        this.stars[i].active = false;
                    }
                }
                this.starHover = 0;
            },
            rating(index) {
                let total = this.stars.length;
                let idx = index + 1;
                if (this.form.rating == 0) {
                    this.form.rating = idx;
                    for (let i = 0; i < idx; i++) {
                        this.stars[i].src = starOnImg;
                        this.stars[i].active = true;
                    }
                } else {
                    if (idx < this.form.rating) {
                        for (let i = idx; i < this.form.rating; i++) {
                            this.stars[i].src = starOffImg;
                            this.stars[i].active = false;
                        }
                    }

                    if (idx >= this.form.rating) {
                        for (let i = 0; i < idx; i++) {
                            this.stars[i].src = starOnImg;
                            this.stars[i].active = true;
                        }
                    }

                    let count = 0;
                    for (let i = 0; i < total; i++) {
                        if (this.stars[i].active) {
                            count++;
                        }
                    }
                    this.form.rating = count;
                }
            },
            onCancle(val) {
                this.showForm = val;
            },
            validateFormItems() {
                if (!this.form.content) {
                    $('.review-form-content').addClass('form-control-error');
                    $('.review-form-content').find('p').addClass('form-error-message');
                    $('.review-form-content').find('p').html(Translator.trans('validate.empty_content_hint'));

                    return false;
                } else {
                    $('.review-form-content').removeClass('form-control-error');
                    $('.review-form-content').find('p').removeClass('form-error-message');
                    $('.review-form-content').find('p').empty();
                }

                if (!this.form.rating) {
                    $('.review-form-rating').addClass('form-control-error');
                    $('.review-form-rating').find('p').addClass('form-error-message');
                    $('.review-form-rating').find('p').html(Translator.trans('validate.raty_star.message'));

                    return false;
                } else {
                    $('.review-form-rating').removeClass('form-control-error');
                    $('.review-form-rating').find('p').removeClass('form-error-message');
                    $('.review-form-rating').find('p').empty();
                }

                if ($("input[name=enable_anti_brush_captcha]").val() == 1 && this.captcha.isShowCaptcha == 1){
                    this.captcha.setType('create-review')
                    this.captcha.showDrag();

                    return false;
                }

                return true;
            },
            onConfirm() {
                if (!this.validateFormItems()) return;

                Api.review.review({
                    data: {
                        'targetType': this.targetType,
                        'targetId': this.targetId,
                        'content': this.form.content,
                        'rating': this.form.rating,
						'_dragCaptchaToken': this._dragCaptchaToken
                    },
                }).then(res => {
                    if (res.error) {
						return;
                    }

                    cd.message({
                        'type': 'success',
                        'message': Translator.trans('site.save_success_hint')
                    });
										
                    window.location.reload();
                }).finally(() => {
                    this.captcha.isShowCaptcha = 1;
                    this.captcha.hideDrag();
                })
            },
            removeHtml(input) {
                return input && input.replace(/<(?:.|\n)*?>/gm, '')
                    .replace(/(&rdquo;)/g, '\"')
                    .replace(/&ldquo;/g, '\"')
                    .replace(/&mdash;/g, '-')
                    .replace(/&nbsp;/g, '')
                    .replace(/&amp;/g, '&')
                    .replace(/&gt;/g, '>')
                    .replace(/&lt;/g, '<')
                    .replace(/<[\w\s"':=\/]*/, '');
            }
        }
    }
</script>
