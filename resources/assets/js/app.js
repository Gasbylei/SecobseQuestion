
/**
 * First we will load all of this project's JavaScript dependencies which
 * include Vue and Vue Resource. This gives a great starting point for
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the body of the page. From here, you may begin adding components to
 * the application, or feel free to tweak this setup for your needs.
 */

Vue.component('example', require('./components/Example.vue'));
Vue.component('user-vote-button', require('./components/UserVoteButton.vue'));
Vue.component('post-follow-button', require('./components/PostFollowButton.vue'));
Vue.component('user-follow-button', require('./components/UserFollowButton.vue'));
Vue.component('send-message', require('./components/SendMessage.vue'));
Vue.component('comment-reply', require('./components/CommentReply.vue'));

const app = new Vue({
    el: '#app'
});
