<template>
  <ion-page>
    <ion-header>
      <ion-toolbar>
        <ion-title>Productos</ion-title>
      </ion-toolbar>
      <ion-toolbar>
        <ion-searchbar v-model="searchQuery" @ionInput="searchProducts" debounce="500"></ion-searchbar>
      </ion-toolbar>
    </ion-header>
    <ion-content class="bg-slate-100">
      <div class="p-4 space-y-4">
        <div
          v-for="product in products"
          :key="product.id"
          class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 flex items-center justify-between gap-4"
        >
          <div>
            <p class="text-lg font-semibold text-slate-800">{{ product.name }}</p>
            <p class="text-sm text-slate-500">{{ product.brand || 'Sin marca' }}</p>
            <p class="text-sm text-slate-500">{{ formatPrice(product.average_price) }}</p>
          </div>
          <button
            type="button"
            class="inline-flex items-center px-3 py-1.5 text-sm font-semibold text-emerald-600 border border-emerald-200 rounded-lg hover:bg-emerald-50"
          >
            Agregar
          </button>
        </div>
      </div>

      <ion-infinite-scroll @ionInfinite="loadMore">
        <ion-infinite-scroll-content></ion-infinite-scroll-content>
      </ion-infinite-scroll>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonSearchbar, IonInfiniteScroll, IonInfiniteScrollContent } from '@ionic/vue';
import api from '../services/api';

const products = ref<any[]>([]);
const searchQuery = ref('');
const page = ref(1);

const fetchProducts = async (reset = false) => {
  if (reset) {
    page.value = 1;
    products.value = [];
  }

  try {
    const response = await api.get('/products', {
      params: {
        page: page.value,
        search: searchQuery.value
      }
    });

    if (reset) {
      products.value = response.data.data;
    } else {
      products.value = [...products.value, ...response.data.data];
    }
  } catch (error) {
    console.error('Error fetching products', error);
  }
};

const searchProducts = () => {
  fetchProducts(true);
};

const loadMore = async (ev: any) => {
  page.value++;
  await fetchProducts();
  ev.target.complete();
};

const formatPrice = (price: number | null) => {
  if (!price) {
    return 'Precio no disponible';
  }

  return `$${price}`;
};

onMounted(() => {
  fetchProducts(true);
});
</script>
