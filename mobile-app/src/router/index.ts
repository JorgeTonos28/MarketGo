import { createRouter, createWebHistory } from '@ionic/vue-router';
import { RouteRecordRaw } from 'vue-router';
import TabsPage from '../views/TabsPage.vue';
import LoginPage from '../views/LoginPage.vue';

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    redirect: '/tabs/lists'
  },
  {
    path: '/login',
    component: LoginPage,
  },
  {
    path: '/tabs/',
    component: TabsPage,
    children: [
      {
        path: '',
        redirect: '/tabs/lists'
      },
      {
        path: 'lists',
        component: () => import('../views/ShoppingListsPage.vue')
      },
      {
        path: 'products',
        component: () => import('../views/ProductsPage.vue')
      }
    ]
  },
  {
    path: '/lists/:id',
    component: () => import('../views/ShoppingListDetailPage.vue')
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
})

router.beforeEach((to, from, next) => {
    const publicPages = ['/login'];
    const authRequired = !publicPages.includes(to.path);
    const loggedIn = localStorage.getItem('auth_token');

    if (authRequired && !loggedIn) {
      return next('/login');
    }

    next();
  });

export default router
