<template>
  <ion-page>
    <ion-header>
      <ion-toolbar>
        <ion-buttons slot="start">
          <ion-back-button default-href="/tabs/lists"></ion-back-button>
        </ion-buttons>
        <ion-title>{{ list?.name }}</ion-title>
      </ion-toolbar>
    </ion-header>
    <ion-content>
      <div v-if="loading" class="ion-padding ion-text-center">
        <ion-spinner></ion-spinner>
      </div>

      <div v-else-if="list">
        <ion-list-header>
          <ion-label>Pendientes</ion-label>
        </ion-list-header>
        <ion-list>
          <ion-item v-for="item in pendingItems" :key="item.id">
            <ion-checkbox slot="start" :checked="false" @ionChange="toggleItem(item, 'in_cart')"></ion-checkbox>
            <ion-label>
              <h2>{{ item.product.name }}</h2>
              <p>{{ item.quantity }} {{ item.quantity_unit }}</p>
            </ion-label>
          </ion-item>
        </ion-list>

        <ion-list-header>
          <ion-label>En el carrito</ion-label>
        </ion-list-header>
        <ion-list>
          <ion-item v-for="item in cartItems" :key="item.id">
            <ion-checkbox slot="start" :checked="true" @ionChange="toggleItem(item, 'pending')"></ion-checkbox>
            <ion-label class="strikethrough">
              <h2>{{ item.product.name }}</h2>
              <p>{{ item.quantity }} {{ item.quantity_unit }}</p>
            </ion-label>
          </ion-item>
        </ion-list>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonButtons, IonBackButton, IonList, IonItem, IonLabel, IonCheckbox, IonListHeader, IonSpinner } from '@ionic/vue';
import api from '../services/api';

const route = useRoute();
const listId = route.params.id;
const list = ref<any>(null);
const loading = ref(true);

const pendingItems = computed(() => {
  return list.value?.items.filter((i: any) => i.status === 'pending') || [];
});

const cartItems = computed(() => {
  return list.value?.items.filter((i: any) => i.status === 'in_cart') || [];
});

const fetchList = async () => {
  try {
    const response = await api.get(`/shopping-lists/${listId}`);
    list.value = response.data;
  } catch (error) {
    console.error('Error fetching list', error);
  } finally {
    loading.value = false;
  }
};

const toggleItem = async (item: any, newStatus: string) => {
  // Optimistic update
  const originalStatus = item.status;
  item.status = newStatus;

  try {
    await api.patch(`/shopping-lists/${listId}/items/${item.id}/status`, {
      status: newStatus
    });
  } catch (error) {
    // Revert on error
    item.status = originalStatus;
    console.error('Error updating item', error);
  }
};

onMounted(() => {
  fetchList();
});
</script>

<style scoped>
.strikethrough h2 {
  text-decoration: line-through;
  color: #888;
}
</style>
