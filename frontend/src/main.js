import './assets/main.css'

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import axios from 'axios'
import i18n from './i18n'

// 設定 axios 基礎 URL
axios.defaults.baseURL = 'http://localhost:8000';  // 後端 API 的基礎 URL

// 添加請求攔截器以處理 CSRF 令牌等
axios.interceptors.request.use(config => {
  return config;
}, error => {
  return Promise.reject(error);
});

// 添加回應攔截器以處理通用錯誤
axios.interceptors.response.use(
  response => response,
  error => {
    // 處理常見錯誤（例如：401、403、500 等）
    if (error.response) {
      if (error.response.status === 401) {
        console.error('未授權訪問');
      } else if (error.response.status === 403) {
        console.error('禁止訪問');
      } else if (error.response.status === 500) {
        console.error('伺服器錯誤');
      }
    } else if (error.request) {
      console.error('無法連接到伺服器');
    } else {
      console.error('發生錯誤', error.message);
    }
    return Promise.reject(error);
  }
);

const app = createApp(App);

app.use(router);
app.use(i18n);
app.mount('#app');
