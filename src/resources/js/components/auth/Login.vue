<template>
  <div class="relative min-h-screen bg-slate-950">
    <div class="absolute inset-0 overflow-hidden">
      <div class="absolute -top-32 -left-20 h-96 w-96 rounded-full bg-indigo-500/40 blur-3xl"></div>
      <div class="absolute -bottom-40 right-0 h-[28rem] w-[28rem] rounded-full bg-purple-500/30 blur-[120px]"></div>
      <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.05),_transparent)]"></div>
    </div>

    <div class="relative flex min-h-screen items-center justify-center px-4 py-16 sm:px-6 lg:px-8">
      <div class="w-full max-w-5xl">
        <div class="grid gap-8 lg:grid-cols-[1.1fr_1fr]">
          <div class="hidden rounded-3xl bg-white/5 px-10 py-12 text-white backdrop-blur-xl shadow-2xl lg:block">
            <div class="space-y-6">
              <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs uppercase tracking-[0.4em] text-indigo-100">
                Welcome
              </span>
              <h1 class="text-3xl font-semibold leading-tight tracking-tight text-white sm:text-4xl">
                {{ brandLabel }}
              </h1>
              <p class="text-sm leading-relaxed text-indigo-100/90">
                区域活動やマンションの訪問記録をよりスムーズに。最新のフィールドツールへアクセスするには、まずログインしてください。
              </p>

              <div class="grid gap-4">
                <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4">
                  <h3 class="text-sm font-semibold text-white/90">区域マップに素早くアクセス</h3>
                  <p class="mt-1 text-xs text-indigo-100/80">
                    ログインすると半径{{ mapRadiusKmText }}kmの範囲内にあるマンションを地図上で確認できます。
                  </p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-4">
                  <h3 class="text-sm font-semibold text-white/90">最新のお知らせをチェック</h3>
                  <p class="mt-1 text-xs text-indigo-100/80">
                    会衆からのお知らせをトップページでまとめて閲覧できます。
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="rounded-3xl bg-white/95 p-8 shadow-2xl shadow-black/20 backdrop-blur">
            <div class="mb-8 space-y-2 text-center">
              <h2 class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">
                オンライン区域へログイン
              </h2>
              <p class="text-sm text-slate-500">
                ご自身のメールアドレスとパスワードを入力してください。
              </p>
            </div>

            <form class="space-y-6" @submit.prevent="handleSubmit">
              <div v-if="error" class="rounded-2xl border border-red-200 bg-red-50/70 px-4 py-3 text-sm text-red-700">
                <div class="flex gap-2">
                  <AlertCircle class="h-5 w-5 shrink-0" />
                  <p>{{ error }}</p>
                </div>
              </div>

              <div class="space-y-5">
                <div>
                  <label for="email" class="block text-sm font-medium text-slate-700">メールアドレス</label>
                  <div class="mt-2 flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm focus-within:border-indigo-400 focus-within:ring-1 focus-within:ring-indigo-300" :class="{ 'border-red-400 ring-1 ring-red-200': errors.email }">
                    <Mail class="mr-3 h-4 w-4 text-slate-400" />
                    <input
                      id="email"
                      v-model="form.email"
                      name="email"
                      type="email"
                      autocomplete="email"
                      required
                      class="w-full border-none bg-transparent text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-0"
                      placeholder="user@example.com"
                    />
                  </div>
                  <p v-if="errors.email" class="mt-1 text-xs text-red-600">{{ errors.email }}</p>
                </div>

                <div>
                  <label for="password" class="block text-sm font-medium text-slate-700">パスワード</label>
                  <div class="mt-2 flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm focus-within:border-indigo-400 focus-within:ring-1 focus-within:ring-indigo-300" :class="{ 'border-red-400 ring-1 ring-red-200': errors.password }">
                    <Lock class="mr-3 h-4 w-4 text-slate-400" />
                    <input
                      id="password"
                      v-model="form.password"
                      name="password"
                      type="password"
                      autocomplete="current-password"
                      required
                      class="w-full border-none bg-transparent text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-0"
                      placeholder="パスワードを入力"
                    />
                  </div>
                  <p v-if="errors.password" class="mt-1 text-xs text-red-600">{{ errors.password }}</p>
                </div>
              </div>

              <div class="flex items-center justify-between text-sm">
                <label for="remember" class="inline-flex cursor-pointer items-center gap-2 text-slate-600">
                  <input
                    id="remember"
                    v-model="form.remember"
                    name="remember"
                    type="checkbox"
                    class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                  />
                  <span>ログイン状態を保存</span>
                </label>
              </div>

              <button
                type="submit"
                :disabled="loading"
                class="relative inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold tracking-wide text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70"
              >
                <span v-if="loading" class="absolute left-4 flex h-5 w-5 items-center justify-center">
                  <svg class="h-5 w-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                ログイン
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed } from 'vue';
import axios from 'axios';
import { Mail, Lock, AlertCircle, Compass, ArrowRight } from 'lucide-vue-next';

export default {
  name: 'Login',
  components: {
    Mail,
    Lock,
    AlertCircle,
    Compass,
    ArrowRight,
  },
  emits: ['login-success'],
  setup(props, { emit }) {
    const form = reactive({
      email: '',
      password: '',
      remember: false,
    });

    const errors = reactive({});
    const error = ref('');
    const loading = ref(false);

    const brandLabel = computed(() => {
      const prefix = import.meta.env.VITE_APP_BRAND_PREFIX || '';
      const base = 'オンライン区域';
      return prefix ? `${prefix} ${base}` : base;
    });

    const mapRadiusRaw = Number.parseFloat(import.meta.env.VITE_BUILDING_MAP_HALF_SIDE_KM ?? '1.0');
    const mapRadiusKmText = Number.isFinite(mapRadiusRaw)
      ? (Number.isInteger(mapRadiusRaw) ? mapRadiusRaw.toFixed(0) : mapRadiusRaw.toFixed(1))
      : '1.0';

    const handleSubmit = async () => {
      Object.keys(errors).forEach((key) => delete errors[key]);
      error.value = '';
      loading.value = true;

      try {
        await axios.get('/sanctum/csrf-cookie');
        const response = await axios.post('/login', form);

        if (response.data.success) {
          emit('login-success');
        } else {
          error.value = response.data.message || '予期しないエラーが発生しました。';
        }
      } catch (e) {
        if (e.response) {
          if (e.response.status === 422) {
            if (e.response.data.errors) {
              const responseErrors = e.response.data.errors;
              Object.keys(responseErrors).forEach((key) => {
                errors[key] = responseErrors[key][0];
              });
            } else if (e.response.data.message) {
              error.value = e.response.data.message;
            }
          } else {
            error.value = e.response.data.message || '認証に失敗しました。入力内容を確認してください。';
          }
        } else {
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
      brandLabel,
      mapRadiusKmText,
      handleSubmit,
    };
  },
};
</script>
