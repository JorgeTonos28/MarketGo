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
    <ion-content class="bg-slate-100">
      <ion-refresher slot="fixed" @ionRefresh="doRefresh">
        <ion-refresher-content></ion-refresher-content>
      </ion-refresher>

      <div class="p-4 space-y-4">
        <router-link
          v-for="list in lists"
          :key="list.id"
          :to="`/lists/${list.id}`"
          class="block bg-white rounded-xl shadow-sm border border-slate-200 p-4"
        >
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-lg font-semibold text-slate-800">{{ list.name }}</p>
              <p class="text-sm text-slate-500">
                {{ list.supermarket ? list.supermarket.name : 'Sin supermercado' }}
              </p>
              <p class="text-sm text-slate-500">{{ list.items_count }} items</p>
            </div>
            <span :class="statusClasses(list.status)">
              {{ statusLabel(list.status) }}
            </span>
          </div>
        </router-link>
      </div>

      <div v-if="lists.length === 0" class="p-6 text-center text-sm text-slate-500">
        <p>No tienes listas de compra.</p>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonRefresher, IonRefresherContent, IonButtons } from '@ionic/vue';
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

const statusLabel = (status: string) => (status === 'active' ? 'Activa' : 'Completada');

const statusClasses = (status: string) => {
  if (status === 'active') {
    return 'inline-flex items-center rounded-full bg-indigo-100 text-indigo-700 px-2.5 py-0.5 text-xs font-medium';
  }

  return 'inline-flex items-center rounded-full bg-slate-100 text-slate-600 px-2.5 py-0.5 text-xs font-medium';
};

onMounted(() => {
  fetchLists();
});
</script>
