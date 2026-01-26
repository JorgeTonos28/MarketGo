<template>
  <ion-page>
    <ion-header>
      <ion-toolbar>
        <ion-title>Login</ion-title>
      </ion-toolbar>
    </ion-header>
    <ion-content class="ion-padding">
      <div class="login-container">
        <ion-item>
          <ion-label position="floating">Email</ion-label>
          <ion-input v-model="email" type="email"></ion-input>
        </ion-item>
        <ion-item>
          <ion-label position="floating">Password</ion-label>
          <ion-input v-model="password" type="password"></ion-input>
        </ion-item>
        <ion-button expand="block" class="ion-margin-top" @click="login">
          Ingresar
        </ion-button>
        <ion-toast
          :is-open="showToast"
          :message="toastMessage"
          :duration="2000"
          @didDismiss="showToast = false"
        ></ion-toast>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonItem, IonLabel, IonInput, IonButton, IonToast } from '@ionic/vue';
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

<style scoped>
.login-container {
  max-width: 400px;
  margin: 0 auto;
  padding-top: 50px;
}
</style>
