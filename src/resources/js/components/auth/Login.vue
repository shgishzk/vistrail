<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          ログイン
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          アカウントにサインインしてください
        </p>
      </div>
      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <div v-if="error" class="rounded-md bg-red-50 p-4">
          <div class="flex">
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">{{ error }}</h3>
            </div>
          </div>
        </div>
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="email" class="sr-only">メールアドレス</label>
            <input
              id="email"
              v-model="form.email"
              name="email"
              type="email"
              autocomplete="email"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              :class="{ 'border-red-500': errors.email }"
              placeholder="メールアドレス"
            />
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
          </div>
          <div>
            <label for="password" class="sr-only">パスワード</label>
            <input
              id="password"
              v-model="form.password"
              name="password"
              type="password"
              autocomplete="current-password"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              :class="{ 'border-red-500': errors.password }"
              placeholder="パスワード"
            />
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
          </div>
        </div>

        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input
              id="remember"
              v-model="form.remember"
              name="remember"
              type="checkbox"
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
            />
            <label for="remember" class="ml-2 block text-sm text-gray-900">
              ログイン状態を保存
            </label>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
              <!-- Loading spinner -->
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            ログイン
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue';
import axios from 'axios';

export default {
  name: 'Login',
  emits: ['login-success'],
  setup(props, { emit }) {
    const form = reactive({
      email: '',
      password: '',
      remember: false
    });
    
    const errors = reactive({});
    const error = ref('');
    const loading = ref(false);

    const handleSubmit = async () => {
      // Reset errors
      Object.keys(errors).forEach(key => delete errors[key]);
      error.value = '';
      loading.value = true;

      try {
        // Get CSRF token
        await axios.get('/sanctum/csrf-cookie');
        
        // Submit login request as JSON
        const response = await axios.post('/login', form);
        
        // Handle successful response
        if (response.data.success) {
          // Emit login success event to parent component
          emit('login-success');
        } else {
          // Handle unexpected success response format
          error.value = response.data.message || '予期しないエラーが発生しました。';
        }
      } catch (e) {
        if (e.response) {
          if (e.response.status === 422) {
            // Validation errors
            if (e.response.data.errors) {
              // Laravel validation error format
              const responseErrors = e.response.data.errors;
              Object.keys(responseErrors).forEach(key => {
                errors[key] = responseErrors[key][0];
              });
            } else if (e.response.data.message) {
              // Single error message
              error.value = e.response.data.message;
            }
          } else {
            // Other error statuses
            error.value = e.response.data.message || '認証に失敗しました。入力内容を確認してください。';
          }
        } else {
          // Network error or other issues
          error.value = 'サーバーに接続できませんでした。インターネット接続を確認してください。';
        }
      } finally {
        loading.value = false;
      }
    };

    return {
      form,
      errors,
      error,
      loading,
      handleSubmit
    };
  }
};
</script>
