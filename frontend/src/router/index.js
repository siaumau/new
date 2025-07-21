import { createRouter, createWebHistory } from 'vue-router'
import HelloWorld from '../components/HelloWorld.vue'
import LocationsView from '../views/LocationsView.vue'
import PosinItemsView from '../views/PosinItemsView.vue'
import PurchaseOrdersView from '../views/PurchaseOrdersView.vue'
import QrCodeView from '../views/QrCodeView.vue'
import QRCodePrintView from '../views/QRCodePrintView.vue'
import MovementHistoryView from '../views/MovementHistoryView.vue'
import ScanPlaceView from '../views/ScanPlaceView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HelloWorld
    },
    {
      path: '/locations',
      name: 'locations',
      component: LocationsView
    },
    {
      path: '/posin/:id/items',
      name: 'posin-items',
      component: PosinItemsView
    },
    {
      path: '/purchase-orders',
      name: 'purchase-orders',
      component: PurchaseOrdersView
    },
    {
      path: '/qr-codes',
      name: 'qr-codes',
      component: QrCodeView
    },
    {
      path: '/qr-print',
      name: 'qr-print',
      component: QRCodePrintView
    },
    {
      path: '/scan-place',
      name: 'scan-place',
      component: ScanPlaceView
    },
    {
      path: '/movement-history',
      name: 'movement-history',
      component: MovementHistoryView
    },
    {
      path: '/products',
      name: 'products',
      component: () => import('../views/ProductsView.vue')
    }
  ]
})

export default router
