<template>
  <ion-page>
    <ion-content class="bg-slate-100">
      <div class="min-h-screen flex items-center justify-center px-6 py-10">
        <div class="w-full max-w-md bg-white shadow-xl rounded-xl p-8">
          <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-indigo-600">MarketGo</h1>
            <p class="text-sm text-slate-500 mt-2">
              Ingresa tus credenciales para continuar con tus compras inteligentes.
            </p>
          </div>
          <form class="space-y-5" @submit.prevent="login">
            <div>
              <label for="email" class="block text-sm font-medium text-slate-600 mb-1">
                Correo electrónico
              </label>
              <input
                id="email"
                v-model="email"
                type="email"
                class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="demo@marketgo.test"
              />
            </div>
            <div>
              <label for="password" class="block text-sm font-medium text-slate-600 mb-1">
                Contraseña
              </label>
              <input
                id="password"
                v-model="password"
                type="password"
                class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="password"
              />
            </div>
            <button
              type="submit"
              class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-indigo-600 text-white font-semibold rounded-lg shadow-sm hover:bg-indigo-500 transition-colors"
            >
              Ingresar
            </button>
          </form>
        </div>
      </div>
      <ion-toast
        :is-open="showToast"
        :message="toastMessage"
        :duration="2000"
        @didDismiss="showToast = false"
      ></ion-toast>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { IonPage, IonContent, IonToast } from '@ionic/vue';
import api from '../services/api';

const email = ref('');
const password = ref('');
const router = useRouter();
const showToast = ref(false);
const toastMessage = ref('');

const login = async () => {
  try {
    const response = await api.post('/login', {
      email: email.value,
      password: password.value,
    });

    localStorage.setItem('auth_token', response.data.token);
    localStorage.setItem('user', JSON.stringify(response.data.user));

    router.replace('/tabs/lists');
  } catch (error) {
    console.error(error);
    toastMessage.value = 'Credenciales incorrectas o error de conexión';
    showToast.value = true;
  }
};
</script>
