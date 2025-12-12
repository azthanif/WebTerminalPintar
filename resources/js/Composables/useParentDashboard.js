// import { computed } from 'vue'

// export function useParentDashboard(summary = {}) {
//   const attendanceRate = computed(() => summary?.attendance_rate ?? 0)
//   const sessionsThisMonth = computed(() => summary?.sessions_this_month ?? 0)
//   const notesThisMonth = computed(() => summary?.notes_this_month ?? 0)
//   const nextSchedule = computed(() => summary?.next_schedule ?? null)

//   return {
//     attendanceRate,
//     sessionsThisMonth,
//     notesThisMonth,
//     nextSchedule,
//   }
// }

import { computed } from 'vue'

export function useParentDashboard(summary = {}) {
  const attendanceRate = computed(() => summary?.attendance_rate ?? 0)
  const sessionsThisMonth = computed(() => summary?.sessions_this_month ?? 0)
  const notesThisMonth = computed(() => summary?.notes_this_month ?? 0)

  const nextSchedule = computed(() => {
    const schedule = summary?.next_schedule
    
    if (!schedule) return null

    let formattedDate = '-'
    
    if (schedule.start_time) {
        const date = new Date(schedule.start_time) // Browser otomatis konversi UTC ke WIB
        
        formattedDate = new Intl.DateTimeFormat('id-ID', {
            weekday: 'long', // Senin
            day: 'numeric',  // 12
            month: 'short',  // Des
            year: 'numeric', // 2024
            hour: '2-digit', // 12
            minute: '2-digit', // 12
        }).format(date)
    }

    return {
        ...schedule,
        start_time_readable: formattedDate
    }
  })

  return {
    attendanceRate,
    sessionsThisMonth,
    notesThisMonth,
    nextSchedule,
  }
}