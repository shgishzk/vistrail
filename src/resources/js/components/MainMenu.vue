<template>
  <div class="min-h-screen bg-gray-100 py-6 flex flex-col justify-center sm:py-12">
    <div class="relative py-3 sm:max-w-xl sm:mx-auto">
      <div class="relative px-4 py-10 bg-white shadow-lg sm:rounded-3xl sm:p-20">
        <div class="max-w-md mx-auto">
          <div class="text-center">
            <h2 class="text-2xl font-semibold text-gray-900">メインメニュー</h2>
            <p class="mt-2 text-gray-600">ようこそ、{{ user ? user.name : 'ゲスト' }}さん</p>
          </div>
          
          <div class="mt-8 space-y-6">
            <div class="grid grid-cols-1 gap-4">
              <a href="#" class="flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                メニュー項目 1
              </a>
              <a href="#" class="flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                メニュー項目 2
              </a>
              <a href="#" class="flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                メニュー項目 3
              </a>
            </div>
            
            <div class="pt-4">
              <button 
                @click="logout" 
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
              >
                ログアウト
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
  name: 'MainMenu',
  setup() {
    const user = ref(null);
    
    const fetchUser = async () => {
      try {
        const response = await axios.get('/api/user');
        user.value = response.data.user;
      } catch (error) {
        console.error('Failed to fetch user data:', error);
      }
    };
    
    const logout = async () => {
      try {
        await axios.post('/logout');
        window.location.reload();
      } catch (error) {
        console.error('Failed to logout:', error);
      }
    };
    
    onMounted(() => {
      fetchUser();
    });
    
    return {
      user,
      logout
    };
  }
};
</script>
