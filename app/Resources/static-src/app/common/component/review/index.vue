<template>
    <div class="reviews">
        <create-review :target-id="targetId" :target-type="targetType" :can-create="canCreate"
                       :user-review="userReview"></create-review>
        <div class="reviews-item" v-for="review in reviews" :key="review.id" :class="'reviews-item-'+ review.id">
            <img class="reviews-item__img" :src="review.user.avatar.large" alt="">
            <div class="reviews-item__text reviews-text">
                <div class="reviews-text__nickname">
                    <a class="link-dark" href="javascript:;" target="_blank">{{ review.user.nickname }}</a>
                    <span>{{ review.target.title }}</span>
                    {{ review.createdTime | createdTime }}
                </div>
                <div class="reviews-text__rating" v-html="$options.filters.rating(review.rating)"></div>
                <div class="reviews-text__content">{{ review.content }}</div>
                <div class="reviews-text__reply">
                    <a href="javascript:;"
                       :data-toggle="'reviews-text__reply-content-'+review.id"
                       @click="switchDisplay">
                        <span v-if="review.posts.length == 0">{{ 'thread.post.reply'|trans }}</span>
                        <span v-else>{{ 'site.data.collapse'|trans }}</span>
                    </a>
                </div>
                <div v-if="review.posts.length == 0" class="reviews-text__reply-content clearfix hidden"
                     :class="'reviews-text__reply-content-'+review.id">
                    <form>
                        <textarea class="post-content" @blur="validatePostContent"></textarea>
                        <p></p>
                        <a href="javascript:;" class="btn btn-sm btn-default plm prm pull-right"
                           @click="onSave($event, review.id)">{{ 'form.btn.save'|trans }}</a>
                    </form>
                </div>
                <div v-else class="reviews-text__reply-content clearfix"
                     :class="'reviews-text__reply-content-'+review.id">
                    <ul class="media-list thread-post-list thread-subpost-list">
                        <li class="thread-post media" :class="'thread-subpost-'+post.id"
                            v-for="post in review.posts" :key="post.id">
                            <div class="media-left">
                                <img class="avatar-sm" :src="post.user.avatar.large" alt="">
                            </div>
                            <div class="media-body">
                                <div class="metas">
                                    <div v-if="canAccess" class="thread-post-manage-dropdown dropdown pull-right">
                                        <a href="javascript:;" class="dropdown-toggle color-gray"
                                           data-toggle="dropdown">
                                            <span class="glyphicon glyphicon-collapse-down"></span>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="javascript:"
                                                   class="js-delete-post"
                                                   :data-target="'thread-subpost-'+post.id"
                                                   @click="onDelete($event, post.id)">{{'site.delete'|trans}}</a>
                                            </li>
                                        </ul>
                                    </div>

                                    <a class="link-dark" href="javascript:;" target="_blank">
                                        {{ post.user.nickname }}
                                    </a>
                                    <span class="bullet">•</span>
                                    <span class="color-gray">{{post.createdTime|smart_time}} </span>
                                </div>
                                <div class="editor-text">{{ post.content }}</div>
                                <div class="ptm pbl"></div>
                            </div>
                        </li>
                    </ul>
                    <a href="javascript:;" class="btn btn-default btn-xs pull-right mbs"
                       :data-toggle="'reviews-text__reply-content-form-'+review.id"
                       @click="onFormDisplay">{{ 'thread.post.reply'|trans }}</a>
                    <form class="hidden" :class="'reviews-text__reply-content-form-'+review.id">
                        <textarea class="post-content" @blur="validatePostContent"></textarea>
                        <p></p>
                        <a href="javascript:;" class="btn btn-sm btn-default plm prm pull-right"
                           @click="onSave($event, review.id)">{{ 'form.btn.save'|trans }}</a>
                    </form>
                </div>
            </div>
        </div>
        <div class="learn-more"><a href="javascript:;">查看更多<i class="es-icon es-icon-chevronright"></i></a>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    import createReview from './src/create-review';

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
        name: 'reviews',
        components: {
            createReview
        },
        data() {
            if (this.targetType && this.targetId) {
                axios.get('/api/reviews', {
                    params: {
                        targetType: this.targetType,
                        targetId: this.targetId,
                        offset: this.offset,
                        limit: this.limit,
                        needPosts: this.needPosts,
                    },
                }).then(response => {
                    this.reviews = response.data.data;
                });
            }

            return {
                postContent: '',
                reviews: [],
            }
        },
        props: {
            targetType: {
                type: String,
                default: null
            },
            targetId: {
                type: Number,
                default: null
            },
            needPosts: {
                type: Boolean,
                default: true
            },
            offset: {
                type: Number,
                default: 0
            },
            limit: {
                type: Number,
                default: 5
            },
            canAccess: {
                type: Boolean,
                default: false,
            },
            canCreate: {
                type: Boolean,
                default: false,
            },
            userReview: {
                type: Object,
                default: null
            }
        },
        methods: {
            switchDisplay(event) {
                let $target = $('.' + $(event.currentTarget).data('toggle'));

                $target.toggleClass('hidden');
                if ($target.hasClass('hidden')) {
                    $(event.currentTarget).html(Translator.trans('thread.post.reply'));
                } else {
                    $(event.currentTarget).html(Translator.trans('site.data.collapse'));
                }
            },
            onFormDisplay(event) {
                if ($(event.currentTarget).siblings('ul').find('.thread-post').length >= 5) {
                    cd.message({
                        type: 'danger',
                        message: Translator.trans('course.manage.post_limit_hint')
                    });
                    return;
                }

                this.switchDisplay(event);
            },
            validatePostContent(event) {
                let $form = $(event.currentTarget).parent('form');
                if (!$form.find('.post-content').val().trim()) {
                    $form.find('.post-content').addClass('form-control-error');
                    $form.find('p').addClass('form-error-message');
                    $form.find('p').html(Translator.trans('validate.empty_content_hint'));

                    return false;
                }

                $form.find('.post-content').removeClass('form-control-error');
                $form.find('p').removeClass('form-error-message');
                $form.find('p').empty();

                return true;
            },
            generateReviewPostLi(post) {
                let html = '<li class="thread-post thread-subpost-' + post.id + ' media">\n' +
                    '  <div class="media-left">\n' +
                    '    <img class="avatar-sm" src="' + post.user.avatar.large + '" alt="">\n' +
                    '  </div>\n' +
                    '  <div class="media-body">\n' +
                    '    <div class="metas">\n';
                if (this.canAccess) {
                    html = html + ' <div class="thread-post-manage-dropdown dropdown pull-right">\n' +
                        '             <a href="javascript:;" class="dropdown-toggle color-gray" data-toggle="dropdown">\n' +
                        '               <span class="glyphicon glyphicon-collapse-down"></span></a>\n' +
                        '             <ul class="dropdown-menu">\n' +
                        '               <li>\n' +
                        '                 <a href="javascript:" class="js-delete-post" data-target="thread-subpost-' + post.id + '" @click="onDelete">' +
                        Translator.trans('site.delete') + '</a></li></ul></div>';
                }

                html = html + '      <a class="link-dark" href="javascript:;" target="_blank">\n' + post.user.nickname +
                    '      </a>\n' +
                    '      <span class="bullet">•</span>\n' +
                    '      <span class="color-gray">' + Translator.trans('site.twig.extension.smarttime.hardly') + '</span>\n' +
                    '    </div>\n' +
                    '    <div class="editor-text">' + post.content + '</div>\n' +
                    '    <div class="ptm pbl"></div>' +
                    '  </div>\n' +
                    '</li>';

                return html;

            },
            onSave(event, reviewId) {
                if (!this.validatePostContent(event)) {
                    return;
                }
                let $targetForm = $(event.currentTarget).parent('form');

                if ($targetForm.siblings('ul').find('.thread-post').length >= 5) {
                    cd.message({
                        type: 'danger',
                        message: Translator.trans('course.manage.post_limit_hint')
                    });
                    return;
                }

                axios({
                    url: "/api/review/" + reviewId + "/post",
                    method: "POST",
                    data: {
                        'content': $targetForm.find('.post-content').val().trim()
                    },
                }).then(res => {
                    let html = this.generateReviewPostLi(res.data);

                    if ($targetForm.siblings('ul').length) {
                        $targetForm.siblings('ul').append(html);
                    } else {
                        html = '<ul class="media-list thread-post-list thread-subpost-list">' + html + '</ul>';
                        $targetForm.before(html);
                    }

                    $targetForm.find('.post-content').val('');

                    cd.message({
                        type: 'success',
                        message: Translator.trans('site.save_success_hint')
                    });
                });
            },
            onDelete(event, reviewId) {
                let $target = $('.' + $(event.currentTarget).data('target'));

                cd.confirm({
                    title: '',
                    content: Translator.trans('thread.post.delete_hint'),
                    okText: Translator.trans('site.confirm'),
                    cancelText: Translator.trans('site.cancel'),
                    className: '',
                }).on('ok', () => {
                    axios({
                        url: "/api/review/" + reviewId,
                        method: 'DELETE',
                    }).then(res => {
                        $target.remove();
                        cd.message({
                            type: 'success',
                            message: Translator.trans('site.delete_success_hint')
                        });
                    });
                }).on('cancel', () => {
                })
            },
        },
        filters: {
            createdTime(date) {
                return date.slice(0, 10);
            },
            rating(score) {
                let floorScore = Math.floor(score);
                let emptyNum = 5 - floorScore;
                let ele = '';
                if (floorScore > 0) {
                    for (let i = 0; i < floorScore; i++) {
                        ele += `<i class="es-icon es-icon-star color-warning"></i>`;
                    }
                }
                if ((score - floorScore) >= 0.5) {
                    ele += `<i class="es-icon es-icon-starhalf color-warning"></i>`;
                }
                if (emptyNum > 0) {
                    for (let i = 0; i < emptyNum; i++) {
                        ele += `<i class="es-icon es-icon-staroutline"></i>`;
                    }
                }
                return ele;
            },
            trans(value, params) {
                if (!value) return '';
                return Translator.trans(value, params);
            },
            smart_time(value) {
                let time = new Date(value);

                let diff = parseInt(new Date().getTime()) / 1000 - parseInt(time.getTime()) / 1000;

                if (diff < 0) {
                    return Translator.trans('site.twig.extension.smarttime.future');
                }

                if (0 == diff) {
                    return Translator.trans('site.twig.extension.smarttime.hardly');
                }

                if (diff < 60) {
                    return Translator.trans('site.twig.extension.smarttime.previous_second', {'diff': Math.round(diff)});
                }

                if (diff < 3600) {
                    return Translator.trans('site.twig.extension.smarttime.previous_minute', {'diff': Math.round(diff / 60)});
                }

                if (diff < 86400) {
                    return Translator.trans('site.twig.extension.smarttime.previous_hour', {'diff': Math.round(diff / 3600)});
                }

                if (diff < 2592000) {
                    return Translator.trans('site.twig.extension.smarttime.previous_day', {'diff': Math.round(diff / 86400)});
                }

                if (diff < 31536000) {
                    return value.slice(4, 5);
                }

                return value.slice(0, 10);
            }
        },
        mounted() {
        }
    }
</script>