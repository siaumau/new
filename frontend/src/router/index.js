import { createRouter, createWebHistory } from 'vue-router'
import PurchaseOrdersView from '../views/PurchaseOrdersView.vue'
import PosinItemsView from '../views/PosinItemsView.vue'

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
    },
    {
      path: '/posin/:id/items',
      name: 'posin-items',
      component: PosinItemsView,
      props: true
    }
  ]
})

export default router
