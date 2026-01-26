<template>
  <ion-page>
    <ion-header>
      <ion-toolbar>
        <ion-title>Mis Listas</ion-title>
        <ion-buttons slot="end">
            <!-- Add button placeholder -->
        </ion-buttons>
      </ion-toolbar>
    </ion-header>
    <ion-content>
      <ion-refresher slot="fixed" @ionRefresh="doRefresh">
        <ion-refresher-content></ion-refresher-content>
      </ion-refresher>

      <ion-list>
        <ion-item v-for="list in lists" :key="list.id" :router-link="'/lists/' + list.id" button detail>
          <ion-label>
            <h2>{{ list.name }}</h2>
            <p>{{ list.supermarket ? list.supermarket.name : 'Sin supermercado' }}</p>
            <p>{{ list.items_count }} items</p>
          </ion-label>
          <ion-badge slot="end" :color="list.status === 'active' ? 'success' : 'medium'">{{ list.status }}</ion-badge>
        </ion-item>
      </ion-list>

      <div v-if="lists.length === 0" class="ion-padding ion-text-center">
        <p>No tienes listas de compra.</p>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonList, IonItem, IonLabel, IonBadge, IonRefresher, IonRefresherContent, IonButtons } from '@ionic/vue';
import api from '../services/api';

const lists = ref<any[]>([]);

const fetchLists = async () => {
  try {
    const response = await api.get('/shopping-lists');
    lists.value = response.data;
  } catch (error) {
    console.error('Error fetching lists', error);
  }
};

const doRefresh = async (event: any) => {
  await fetchLists();
  event.target.complete();
};

onMounted(() => {
  fetchLists();
});
</script>
