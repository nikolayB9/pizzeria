import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;
window.axios.defaults.withXSRFToken = true;
window.axios.defaults.baseURL = 'http://localhost:8082'

window.axios.interceptors.request.use((config) => {
    if (!config.url?.startsWith('http')) {
        const token = (document.cookie.match('(^|; )' + encodeURIComponent('XSRF-TOKEN') + '=([^;]+)') || []).pop() || null;
        if (token) {
            config.headers['X-XSRF-TOKEN'] = token;
        }
    }
    return config;
});

window.axios.interceptors.response.use({}, err => {
    if (err.response.status === 401 || err.response.status === 419) {
        const token = localStorage.getItem('x_xsrf_token')
        if (token) {
            localStorage.removeItem('x_xsrf_token')
        }
        router.push({name: 'user.login'})
    }
    return Promise.reject(err);
})
