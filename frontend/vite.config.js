import { fileURLToPath, URL } from 'node:url'
import { networkInterfaces } from 'os'

import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

// 自動取得本機IP地址
function getLocalIP() {
  const interfaces = networkInterfaces()
  for (const name of Object.keys(interfaces)) {
    for (const iface of interfaces[name]) {
      // 跳過內部地址、非IPv4地址
      if (iface.family === 'IPv4' && !iface.internal) {
        return iface.address
      }
    }
  }
  return 'localhost' // 找不到時回退到localhost
}

// https://vite.dev/config/
export default defineConfig(({ mode }) => {
  // 載入環境變數
  const env = loadEnv(mode, process.cwd(), '')
  
  // 取得本機IP
  const localIP = getLocalIP()
  
  // 決定API目標URL
  const getApiTarget = () => {
    if (env.VITE_API_TARGET) {
      return env.VITE_API_TARGET
    }
    // 自動使用本機IP和8000端口
    return `http://${localIP}:8000`
  }

  const apiTarget = getApiTarget()
  console.log(`🚀 API代理目標: ${apiTarget}`)
  console.log(`📡 本機IP: ${localIP}`)

  return {
    plugins: [
      vue(),
      vueDevTools()
    ],
    resolve: {
      alias: {
        '@': fileURLToPath(new URL('./src', import.meta.url))
      },
    },
    server: {
      host: '0.0.0.0',
      port: 3000,
      https: false,
      proxy: {
        '/api': {
          target: apiTarget,
          changeOrigin: true,
          secure: false
        }
      }
    },
  }
})
