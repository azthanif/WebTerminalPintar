import { computed } from 'vue'

export function useParentDashboard(summary = {}) {
  const attendanceRate = computed(() => summary?.attendance_rate ?? 0)
  const sessionsThisMonth = computed(() => summary?.sessions_this_month ?? 0)
  const notesThisMonth = computed(() => summary?.notes_this_month ?? 0)
  const nextSchedule = computed(() => summary?.next_schedule ?? null)

  return {
    attendanceRate,
    sessionsThisMonth,
    notesThisMonth,
    nextSchedule,
  }
}
