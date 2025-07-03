import { createRouter, createWebHistory } from 'vue-router'
import PurchaseOrdersView from '../views/PurchaseOrdersView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/purchase-orders'
    },
    {
      path: '/purchase-orders',
      name: 'purchase-orders',
      component: PurchaseOrdersView
    }
  ]
})

export default router
