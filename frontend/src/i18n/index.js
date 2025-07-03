import { createI18n } from 'vue-i18n'
import zh_TW from './locales/zh-TW'
import en from './locales/en'

const i18n = createI18n({
  legacy: false, // 使用 Composition API 模式
  locale: localStorage.getItem('locale') || 'zh-TW', // 預設使用繁體中文
  fallbackLocale: 'zh-TW', // 若翻譯鍵缺失，回退到繁體中文
  messages: {
    'zh-TW': zh_TW,
    'en': en
  }
})

export default i18n
