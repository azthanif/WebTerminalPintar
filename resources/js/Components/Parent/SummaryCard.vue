<script setup>
const props = defineProps({
  title: String,
  subtitle: String,
  value: [String, Number],
  accent: {
    type: String,
    default: 'emerald',
  },
})

const accentClass = {
  emerald: 'from-emerald-500/10 to-emerald-500/0 text-emerald-700',
  amber: 'from-amber-500/10 to-amber-500/0 text-amber-700',
  sky: 'from-sky-500/10 to-sky-500/0 text-sky-700',
  rose: 'from-rose-500/10 to-rose-500/0 text-rose-700',
}[props.accent] ?? 'from-slate-500/10 to-slate-500/0 text-slate-700'
</script>

<template>
  <div class="rounded-3xl border border-slate-100 bg-white px-6 py-5 shadow-sm">
    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ subtitle }}</p>
    <h3 class="mt-2 text-xl font-bold text-slate-900">{{ title }}</h3>
    <div :class="['mt-4 rounded-2xl bg-gradient-to-br p-4 text-4xl font-black', accentClass]">
      {{ value }}
    </div>
  </div>
</template>
