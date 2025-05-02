import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.axios.defaults.withCredentials = true;
window.axios.defaults.withXSRFToken = true;

window.axios.interceptors.request.use((config) => {
    if (!config.url?.startsWith('http')) {
        const token = (document.cookie.match('(^|; )' + encodeURIComponent('XSRF-TOKEN') + '=([^;]+)') || []).pop() || null;
        if (token) {
            config.headers['X-XSRF-TOKEN'] = token;
        }
    }
    return config;
});
