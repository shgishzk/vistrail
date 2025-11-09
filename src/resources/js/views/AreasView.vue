<template>
  <div class="space-y-10 lg:space-y-14">
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-600 via-sky-500 to-cyan-500 text-white shadow-2xl">
      <div class="absolute inset-0 opacity-20">
        <div class="absolute -top-10 left-10 h-40 w-40 rounded-full bg-white/30 blur-3xl"></div>
        <div class="absolute bottom-0 right-0 h-60 w-60 rounded-full bg-white/20 blur-3xl"></div>
      </div>
      <div class="relative px-6 py-10 sm:px-10 lg:px-14 lg:py-14">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
          <div class="space-y-4">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-white/70">Areas</p>
            <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">
              区域メニュー
            </h1>
            <p class="max-w-2xl text-base text-white/90">
              担当区域の確認から、新しい区域の受け取りまで、全ての区域フローをここから始めましょう。
            </p>
          </div>
          <div class="rounded-2xl bg-white/10 px-6 py-4 text-sm leading-relaxed text-white/90 shadow-lg shadow-black/10 backdrop-blur">
            <p class="font-semibold text-white">区域のワークフロー</p>
            <ul class="mt-3 space-y-2 text-white/80">
              <li class="flex items-center gap-3">
                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500/80 text-xs font-bold">1</span>
                区域を選ぶ / 受け取る
              </li>
              <li class="flex items-center gap-3">
                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500/80 text-xs font-bold">2</span>
                訪問・記録
              </li>
              <li class="flex items-center gap-3">
                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500/80 text-xs font-bold">3</span>
                区域を返却
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <section>
      <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <div>
          <h2 class="text-2xl font-semibold text-slate-900">区域メニュー</h2>
          <p class="text-sm text-slate-500">以下のメニューより選択してください。</p>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <article
          v-for="item in areaMenu"
          :key="item.to"
          class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:border-sky-300 hover:shadow-xl"
        >
          <div class="flex items-start gap-4">
            <div class="rounded-2xl bg-sky-50 p-3 text-sky-600 transition group-hover:bg-sky-100 group-hover:text-sky-700">
              <component :is="item.icon" class="h-6 w-6" />
            </div>
            <div class="flex-1 space-y-2">
              <div class="flex flex-wrap items-center gap-3">
                <h3 class="text-lg font-semibold text-slate-900">{{ item.title }}</h3>
                <span v-if="item.badge" class="rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wide text-sky-700">
                  {{ item.badge }}
                </span>
              </div>
              <p class="text-sm leading-relaxed text-slate-600">{{ item.description }}</p>
            </div>
          </div>
          <div class="mt-5 flex items-center justify-between border-t border-dashed border-slate-200 pt-4 text-sm text-slate-500">
            <div>
              <p class="font-semibold text-slate-900">{{ item.actionLabel }}</p>
            </div>
            <RouterLink
              :to="item.to"
              class="inline-flex items-center gap-2 rounded-full bg-sky-600 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow transition hover:bg-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:ring-offset-1"
            >
              進む
            </RouterLink>
          </div>
        </article>
      </div>
    </section>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { UserCircle2, Sparkles, Shuffle, Globe2 } from 'lucide-vue-next';

const areaMenu = [
  {
    title: '自分の区域',
    to: '/areas/my',
    description: '割り当てられている区域の訪問や、次のアクションの管理を行います。',
    actionLabel: '割り当てられた区域を表示する',
    icon: UserCircle2,
    badge: '',
  },
  {
    title: '区域を選ぶ',
    to: '/areas/pickup',
    description: '空いている区域の一覧から訪問したい区域を選択して、訪問を開始します。',
    actionLabel: '空いている区域の一覧を見る',
    icon: Sparkles,
  },
  {
    title: '区域を受け取る',
    to: '/areas/accept-reassign',
    description: '司会者や他の奉仕者から区域を受け取り、訪問を開始します。',
    actionLabel: '受け取り可能な区域を見る',
    icon: Shuffle,
  },
  {
    title: 'すべての区域を閲覧',
    to: '/areas/explore',
    description: '会衆内で扱っている全区域を地図とリストで確認し、状況を俯瞰できます。',
    actionLabel: '区域一覧を開く',
    icon: Globe2,
  },
];
</script>
