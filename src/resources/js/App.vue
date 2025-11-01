<template>
  <div>
    <template v-if="isLoading">
      <div class="min-h-screen flex items-center justify-center">
        <svg class="animate-spin h-10 w-10 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>
    </template>
    <template v-else>
      <Login v-if="!isAuthenticated" @login-success="checkAuth" />
      <Layout v-else>
        <MainMenu />
      </Layout>
    </template>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Login from './components/auth/Login.vue';
import Layout from './components/Layout.vue';
import MainMenu from './components/MainMenu.vue';

export default {
  name: 'App',
  components: {
    Login,
    Layout,
    MainMenu
  },
  setup() {
    const isAuthenticated = ref(false);
    const isLoading = ref(true);

    const checkAuth = async () => {
      isLoading.value = true;
      try {
        const response = await axios.get('/api/user');
        isAuthenticated.value = !!response.data.user;
      } catch (error) {
        isAuthenticated.value = false;
        console.error('Authentication check failed:', error);
      } finally {
        isLoading.value = false;
      }
    };

    onMounted(() => {
      checkAuth();
    });

    return {
      isAuthenticated,
      isLoading,
      checkAuth
    };
  }
};
</script>
