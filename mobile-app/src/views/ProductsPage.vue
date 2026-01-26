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
    <ion-content>
      <ion-list>
        <ion-item v-for="product in products" :key="product.id">
          <ion-label>
            <h2>{{ product.name }}</h2>
            <p>{{ product.brand }} - {{ product.average_price ? '$' + product.average_price : 'N/A' }}</p>
          </ion-label>
        </ion-item>
      </ion-list>

      <ion-infinite-scroll @ionInfinite="loadMore">
        <ion-infinite-scroll-content></ion-infinite-scroll-content>
      </ion-infinite-scroll>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonSearchbar, IonList, IonItem, IonLabel, IonInfiniteScroll, IonInfiniteScrollContent } from '@ionic/vue';
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

onMounted(() => {
  fetchProducts(true);
});
</script>
