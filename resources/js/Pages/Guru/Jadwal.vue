<template>
  <div class="space-y-8">
    <Head title="Jadwal & Materi" />

    <!-- Header Section -->
    <section class="flex flex-col gap-6 md:flex-row md:items-end justify-between">
      <div>
         <div class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1 text-xs font-bold text-orange-600 mb-2 border border-orange-100">
            <ClockIcon class="h-3 w-3" />
            <span>Manajemen Jadwal</span>
        </div>
        <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight leading-tight">
             Jadwal & <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-amber-600">Materi</span>
        </h1>
        <p class="mt-2 text-slate-500 font-medium text-lg max-w-2xl">
            Atur jadwal pelajaran dan distribusikan materi pembelajaran.
        </p>
      </div>

      <div class="flex flex-col sm:flex-row gap-3">
           <label class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-600 cursor-pointer hover:bg-slate-50 transition-colors">
            <input
              v-model="filters.withTrashed"
              type="checkbox"
              class="h-4 w-4 rounded border-slate-300 text-orange-500 focus:ring-orange-500"
            />
            <span>Tampilkan Terhapus</span>
          </label>
           <button
            class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-br from-orange-500 to-amber-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-orange-200 transition-all hover:bg-gradient-to-br hover:from-orange-600 hover:to-amber-600 hover:scale-105 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:scale-100"
            :disabled="!hasStudents"
            @click="openAddSchedule"
          >
            <div class="rounded-lg bg-white/20 p-1 group-hover:bg-white/30 transition-colors">
                <PlusIcon class="h-5 w-5 text-white" />
            </div>
            <span>Buat Jadwal Baru</span>
          </button>
      </div>
    </section>

    <!-- Filters & Stats -->
    <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
         <!-- Search -->
        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm group hover:border-orange-200 transition-colors">
             <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 group-hover:text-orange-500 transition-colors">Pencarian</label>
             <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <MagnifyingGlassIcon class="h-5 w-5" />
                </div>
                <input
                    v-model="filters.search"
                    type="search"
                    placeholder="Cari topik atau mapel..."
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 pl-11 pr-4 py-3 text-sm font-medium focus:border-orange-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-orange-100 transition-all hover:bg-white"
                />
            </div>
        </div>

        <!-- Status Filter -->
        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm group hover:border-orange-200 transition-colors">
             <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 group-hover:text-orange-500 transition-colors">Filter Status</label>
             <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <FunnelIcon class="h-5 w-5" />
                </div>
                 <select
                    v-model="filters.status"
                    class="w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50 pl-11 pr-10 py-3 text-sm font-bold text-slate-700 focus:border-orange-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-orange-100 transition-all cursor-pointer hover:bg-white"
                >
                    <option v-for="status in props.statusOptions" :key="status" :value="status">
                    {{ status }}
                    </option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Stats -->
        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-between group hover:border-orange-200 transition-colors hover:shadow-lg">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest group-hover:text-orange-500 transition-colors">Total Jadwal</label>
                <p class="mt-1 text-3xl font-extrabold text-slate-800">{{ jadwalMateri.length }}</p>
            </div>
            <div class="h-14 w-14 rounded-full bg-orange-50 border border-orange-100 flex items-center justify-center text-orange-600 group-hover:scale-110 transition-transform shadow-sm">
                <CalendarIcon class="h-7 w-7" />
            </div>
        </div>
    </section>

    <!-- Schedule List -->
    <section class="space-y-6">
       <!-- Alerts -->
       <div v-if="successMessage" class="rounded-2xl bg-emerald-50 border border-emerald-100 p-4 flex items-center gap-3">
             <CheckCircleIcon class="h-6 w-6 text-emerald-500" />
            <p class="text-sm font-bold text-emerald-700">{{ successMessage }}</p>
        </div>
         <div v-if="errorMessage" class="rounded-2xl bg-rose-50 border border-rose-100 p-4 flex items-center gap-3">
             <ExclamationTriangleIcon class="h-6 w-6 text-rose-500" />
            <p class="text-sm font-bold text-rose-700">{{ errorMessage }}</p>
        </div>

        <div v-if="isLoadingJadwal" class="grid grid-cols-1 md:grid-cols-2 gap-6">
             <div v-for="n in 2" :key="n" class="h-48 animate-pulse rounded-[2.5rem] bg-slate-100"></div>
        </div>

        <div v-else class="grid grid-cols-1 gap-6">
             <article
                v-for="schedule in jadwalMateri"
                :key="schedule.id"
                :class="[
                    'group relative rounded-[2.5rem] p-8 shadow-sm transition-all hover:shadow-lg',
                    schedule.is_deleted ? 'border-rose-100 bg-rose-50/30' : 'border border-slate-200 bg-white hover:border-orange-200'
                ]"
             >
                <div class="flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">
                     <!-- Left Content (Main Info) -->
                    <div class="flex-1 space-y-5">
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="inline-flex items-center gap-2 rounded-lg bg-indigo-50 px-3 py-1.5 min-w-[140px] border border-indigo-100">
                                <CalendarIcon class="h-4 w-4 text-indigo-500" />
                                <span class="text-xs font-bold text-indigo-700 uppercase tracking-wide">{{ formatFullDate(schedule.waktu_mulai) }}</span>
                            </div>
                            <span :class="['rounded-lg px-3 py-1.5 text-xs font-bold border shadow-sm', badgeClass(schedule.status)]">
                                {{ schedule.status }}
                            </span>
                             <span v-if="schedule.is_deleted" class="rounded-lg bg-rose-100 px-3 py-1.5 text-xs font-bold text-rose-700 border border-rose-200">
                                Terhapus
                            </span>
                        </div>

                        <div>
                            <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight group-hover:text-orange-600 transition-colors">{{ schedule.topik }}</h3>
                            <p class="text-sm font-bold text-slate-500 mt-1 flex items-center gap-2">
                                <span class="inline-block h-2 w-2 rounded-full bg-slate-300"></span>
                                {{ schedule.subject }}
                                <span class="text-slate-300 font-normal">•</span>
                                <span class="text-slate-400 font-normal">
                                    {{ schedule.peserta.length ? `${schedule.peserta.length} Siswa Terdaftar` : 'Belum ada peserta' }}
                                </span>
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-4 text-sm font-medium text-slate-600">
                            <div class="flex items-center gap-2 bg-slate-50 px-3 py-2 rounded-xl border border-slate-100">
                                <ClockIcon class="h-5 w-5 text-orange-500" />
                                <span>{{ formatTimeRange(schedule.waktu_mulai, schedule.waktu_selesai) }}</span>
                            </div>
                            <div class="flex items-center gap-2 bg-slate-50 px-3 py-2 rounded-xl border border-slate-100" v-if="schedule.lokasi">
                                <MapPinIcon class="h-5 w-5 text-orange-500" />
                                <span>{{ schedule.lokasi }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions (Revised Layout) -->
                    <div class="w-full lg:w-96 flex flex-col gap-3">
                         <!-- Primary Action (Big Button) -->
                         <button
                            class="w-full flex items-center justify-center gap-3 rounded-xl bg-orange-500 px-5 py-3 text-sm font-bold text-white shadow-md shadow-orange-200 transition-all hover:bg-orange-600 hover:shadow-lg hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="schedule.is_deleted"
                            @click="goToAttendance(schedule)"
                        >
                            <ClipboardDocumentCheckIcon class="h-5 w-5" />
                            Catat Kehadiran
                        </button>
                        
                        <!-- Secondary Actions (Grid) -->
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-600 transition-all hover:bg-slate-50 hover:border-slate-300 active:scale-95 disabled:opacity-50"
                                :disabled="schedule.is_deleted"
                                @click="openEditSchedule(schedule)"
                            >
                                Edit Jadwal
                            </button>
                            <button
                                class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-600 transition-all hover:bg-slate-50 hover:border-slate-300 active:scale-95 disabled:opacity-50"
                                :disabled="schedule.is_deleted"
                                @click="openEditMateri(schedule)"
                            >
                                Kelola Materi
                            </button>
                             <button
                                v-if="!schedule.is_deleted"
                                class="col-span-2 flex items-center justify-center gap-2 rounded-xl border border-rose-100 bg-rose-50 px-4 py-2.5 text-xs font-bold text-rose-600 transition-all hover:bg-rose-100 active:scale-95 disabled:opacity-60"
                                :disabled="scheduleActionState.deleting[schedule.id]"
                                @click="deleteSchedule(schedule)"
                            >
                                 <TrashIcon class="h-4 w-4" />
                                <span>{{ scheduleActionState.deleting[schedule.id] ? 'Menghapus...' : 'Hapus Jadwal' }}</span>
                            </button>
                             <button
                                v-else
                                class="col-span-2 flex items-center justify-center gap-2 rounded-xl border border-sky-100 bg-sky-50 px-4 py-2.5 text-xs font-bold text-sky-600 transition-all hover:bg-sky-100 active:scale-95 disabled:opacity-60"
                                :disabled="scheduleActionState.restoring[schedule.id]"
                                @click="restoreSchedule(schedule)"
                            >
                                 <ArrowPathIcon class="h-4 w-4" />
                                <span>{{ scheduleActionState.restoring[schedule.id] ? 'Memulihkan...' : 'Pulihkan Jadwal' }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Materials Section -->
                <div class="mt-8 border-t border-slate-100 pt-6">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <DocumentArrowUpIcon class="h-4 w-4" />
                        Materi Pembelajaran ({{ schedule.materi.length }})
                    </h4>
                     <div v-if="!schedule.materi.length" class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/30 p-6 text-center">
                        <p class="text-sm font-medium text-slate-400">Belum ada materi diunggah.</p>
                    </div>
                     <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <button
                            v-for="file in schedule.materi"
                            :key="file.id"
                            type="button"
                            class="group/file flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 hover:border-amber-400 hover:ring-2 hover:ring-amber-100 transition-all text-left shadow-sm"
                            @click="openMaterialPreview(file)"
                        >
                             <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-amber-100 to-orange-50 border border-amber-100 flex items-center justify-center text-amber-600 shadow-sm group-hover/file:scale-110 transition-transform">
                                <DocumentTextIcon class="h-6 w-6" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-800 truncate group-hover/file:text-amber-700 transition-colors">{{ file.judul }}</p>
                                <p class="text-xs text-slate-500 mt-0.5 line-clamp-1">{{ file.deskripsi || 'Tanpa deskripsi' }}</p>
                                <div class="mt-2 flex items-center gap-2">
                                     <span :class="['text-[10px] px-2 py-0.5 rounded-full font-bold border', badgeClass(file.status)]">
                                        {{ file.status || 'Terunggah' }}
                                    </span>
                                    <span v-if="file.url" class="text-[10px] font-bold text-amber-600 underline decoration-amber-300">Lihat File</span>
                                </div>
                            </div>
                        </button>
                     </div>
                </div>
             </article>

             <div v-if="!jadwalMateri.length" class="py-20 flex flex-col items-center justify-center text-center">
                <div class="h-20 w-20 bg-slate-50 rounded-full flex items-center justify-center mb-6 shadow-inset">
                    <CalendarIcon class="h-10 w-10 text-slate-300" />
                </div>
                <h3 class="text-lg font-bold text-slate-800">Tidak ada jadwal ditemukan</h3>
                <p class="text-slate-500 font-medium">Buat jadwal baru untuk memulai kegiatan.</p>
            </div>
        </div>
    </section>

    <!-- Modal Tambah Jadwal -->
    <Modal :show="showAddModal" title="Buat Jadwal Baru" variant="primary" max-width="2xl" @close="closeAddSchedule">
         <template #description>Jadwalkan kegiatan belajar mengajar baru.</template>
         <form class="space-y-6" @submit.prevent="submitAddSchedule">
             <!-- Form Fields Container -->
             <div class="space-y-5">
                 <div class="p-4 rounded-xl bg-orange-50/50 border border-orange-100">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Siswa <span class="text-rose-500">*</span></label>
                    <p v-if="!hasStudents" class="text-xs font-bold text-rose-500 bg-rose-50 p-2 rounded">Belum ada data siswa.</p>
                     <div v-else class="flex flex-wrap gap-2 max-h-40 overflow-y-auto custom-scrollbar">
                        <label
                            v-for="student in props.students"
                            :key="student.id"
                            class="inline-flex cursor-pointer select-none items-center gap-2 rounded-xl border bg-white px-3 py-1.5 text-xs font-bold transition-all hover:bg-slate-50 hover:shadow-xs"
                             :class="addForm.student_ids.includes(student.id) ? 'border-orange-200 bg-orange-50 text-orange-700 ring-1 ring-orange-200' : 'border-slate-200 text-slate-600'"
                        >
                            <input
                            v-model="addForm.student_ids"
                            :value="student.id"
                            type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-500"
                            />
                            <span>{{ student.name }}</span>
                        </label>
                    </div>
                 </div>

                 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Mapel</label>
                        <input v-model="addForm.subject" required type="text" placeholder="e.g. Matematika" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-orange-500 focus:ring-4 focus:ring-orange-100 outline-none transition-all" />
                    </div>
                     <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Topik</label>
                        <input v-model="addForm.topic" required type="text" placeholder="e.g. Aljabar Linear" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-orange-500 focus:ring-4 focus:ring-orange-100 outline-none transition-all" />
                    </div>
                 </div>

                 <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Tanggal</label>
                    <input v-model="addForm.tanggal" required type="date" :min="minScheduleDate" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-orange-500 focus:ring-4 focus:ring-orange-100 outline-none transition-all" />
                 </div>

                 <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Mulai</label>
                        <input v-model="addForm.jam_mulai" required type="time" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-orange-500 focus:ring-4 focus:ring-orange-100 outline-none transition-all" />
                    </div>
                     <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Selesai</label>
                        <input v-model="addForm.jam_selesai" required type="time" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-orange-500 focus:ring-4 focus:ring-orange-100 outline-none transition-all" />
                    </div>
                 </div>
                 <p v-if="addTimeError" class="text-xs font-bold text-rose-500">{{ addTimeError }}</p>

                 <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Deskripsi (Opsional)</label>
                    <textarea v-model="addForm.description" rows="2" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-orange-500 focus:ring-4 focus:ring-orange-100 outline-none resize-none transition-all"></textarea>
                 </div>

                 <!-- Material Upload -->
                 <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Upload Materi</label>
                    <label :for="addMaterialInputId" class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer hover:border-orange-400 hover:bg-white transition-all">
                        <DocumentArrowUpIcon class="h-8 w-8 text-slate-400 mb-2" />
                        <span class="text-sm font-medium text-slate-600">Klik untuk pilih file</span>
                        <input :id="addMaterialInputId" ref="materialInput" type="file" class="hidden" multiple @change="handleMaterialFiles($event, addForm)" />
                    </label>
                    
                     <div v-if="addForm.materi_uploads.length" class="mt-4 space-y-2">
                         <div v-for="file in addForm.materi_uploads" :key="file.name" class="flex items-center justify-between p-2 bg-white border border-slate-200 rounded-lg text-xs font-medium shadow-sm">
                             <span class="truncate max-w-[200px]">{{ file.name }}</span>
                             <button type="button" class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 px-2 py-1 rounded" @click="removeMaterialFile(addForm, file)">Batal</button>
                         </div>
                     </div>
                 </div>
             </div>

             <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50" @click="closeAddSchedule">Batal</button>
                <button type="submit" class="rounded-xl bg-orange-500 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-orange-200 hover:bg-orange-600 active:scale-95 transition-transform">Simpan Jadwal</button>
             </div>
         </form>
    </Modal>

    <!-- Modal Edit Jadwal -->
    <Modal :show="showEditScheduleModal" title="Edit Jadwal" variant="info" max-width="2xl" @close="closeEditSchedule">
         <template #description>Perbarui informasi jadwal kegiatan.</template>
         <form class="space-y-6" @submit.prevent="submitEditSchedule">
              <div class="space-y-5">
                 <div class="p-4 rounded-xl bg-sky-50/50 border border-sky-100">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Update Siswa</label>
                     <div v-if="hasStudents" class="flex flex-wrap gap-2 max-h-40 overflow-y-auto custom-scrollbar">
                        <label
                            v-for="student in props.students"
                            :key="student.id"
                            class="inline-flex cursor-pointer select-none items-center gap-2 rounded-xl border bg-white px-3 py-1.5 text-xs font-bold transition-all hover:bg-slate-50"
                             :class="editForm.student_ids.includes(student.id) ? 'border-sky-200 bg-sky-50 text-sky-700 ring-1 ring-sky-200' : 'border-slate-200 text-slate-600'"
                        >
                            <input
                            v-model="editForm.student_ids"
                            :value="student.id"
                            type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                            />
                            <span>{{ student.name }}</span>
                        </label>
                    </div>
                 </div>

                 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Mapel</label>
                        <input v-model="editForm.subject" required type="text" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-sky-500 focus:ring-4 focus:ring-sky-100 outline-none transition-all" />
                    </div>
                     <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Topik</label>
                        <input v-model="editForm.topic" required type="text" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-sky-500 focus:ring-4 focus:ring-sky-100 outline-none transition-all" />
                    </div>
                 </div>

                 <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Tanggal</label>
                    <input v-model="editForm.tanggal" required type="date" :min="minScheduleDate" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-sky-500 focus:ring-4 focus:ring-sky-100 outline-none transition-all" />
                 </div>

                 <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Mulai</label>
                        <input v-model="editForm.jam_mulai" required type="time" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-sky-500 focus:ring-4 focus:ring-sky-100 outline-none transition-all" />
                    </div>
                     <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Selesai</label>
                        <input v-model="editForm.jam_selesai" required type="time" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-sky-500 focus:ring-4 focus:ring-sky-100 outline-none transition-all" />
                    </div>
                 </div>
                 <p v-if="editTimeError" class="text-xs font-bold text-rose-500">{{ editTimeError }}</p>
                 
                  <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Deskripsi</label>
                    <textarea v-model="editForm.description" rows="2" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-sky-500 focus:ring-4 focus:ring-sky-100 outline-none resize-none transition-all"></textarea>
                 </div>
              </div>

             <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50" @click="closeEditSchedule">Batal</button>
                <button type="submit" class="rounded-xl bg-sky-500 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-sky-200 hover:bg-sky-600 active:scale-95 transition-transform">Update Jadwal</button>
             </div>
         </form>
    </Modal>

    <!-- Modal Edit Materi -->
    <Modal :show="showEditMateriModal" title="Kelola Materi" variant="warning" max-width="2xl" @close="closeEditMateri">
        <template #description>Tambah, hapus atau update materi pembelajaran.</template>
        
        <form class="space-y-6" @submit.prevent="submitEditMateri">
             <div class="space-y-4">
                 <div v-for="material in materiForm.existing" :key="material.id" class="rounded-2xl border border-slate-200 p-4 bg-slate-50/50 hover:bg-white hover:shadow-sm transition-all">
                     <div class="flex justify-between items-start mb-3">
                         <div>
                             <p class="text-xs font-bold uppercase text-slate-400">Judul</p>
                             <input v-model="material.judul" class="mt-1 w-full bg-white rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-bold focus:ring-2 focus:ring-amber-200 outline-none" />
                         </div>
                          <button type="button" class="text-xs font-bold text-rose-500 bg-rose-50 px-3 py-1.5 rounded-lg border border-rose-100 hover:bg-rose-100 transition-colors" @click="confirmDeleteMaterial(material)">Hapus</button>
                     </div>
                     <div class="mb-3">
                          <p class="text-xs font-bold uppercase text-slate-400">Deskripsi</p>
                          <input v-model="material.deskripsi" class="mt-1 w-full bg-white rounded-lg border border-slate-200 px-3 py-1.5 text-sm focus:ring-2 focus:ring-amber-200 outline-none" />
                     </div>
                      <div>
                          <p class="text-xs font-bold uppercase text-slate-400 mb-1">Ganti File</p>
                          <input type="file" class="text-xs text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100" @change="onReplaceFile($event, material)" />
                          <p v-if="material.newFile" class="text-xs font-bold text-emerald-600 mt-1">File baru dipilih: {{ material.newFile.name }}</p>
                      </div>
                 </div>

                 <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                     <p class="text-sm font-bold text-amber-800 mb-2">Upload Materi Baru</p>
                     <label :for="editMaterialInputId" class="block cursor-pointer rounded-xl border border-amber-300 bg-white p-3 text-center hover:bg-amber-100 transition-all border-dashed">
                         <span class="text-sm font-bold text-amber-600">+ Pilih File Baru</span>
                         <input :id="editMaterialInputId" ref="newMaterialInput" type="file" class="hidden" multiple @change="handleMaterialFiles($event, materiForm)" />
                     </label>
                      <div v-if="materiForm.materi_uploads.length" class="mt-3 space-y-2">
                         <div v-for="file in materiForm.materi_uploads" :key="file.name" class="flex justify-between items-center text-xs font-bold bg-white p-3 rounded-lg border border-amber-100 shadow-sm">
                             <span class="text-slate-700">{{ file.name }}</span>
                              <button type="button" class="text-rose-500 hover:bg-rose-50 px-2 py-1 rounded" @click="removeMaterialFile(materiForm, file)">Batal</button>
                         </div>
                     </div>
                 </div>
             </div>

             <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50" @click="closeEditMateri">Selesai</button>
                <button type="submit" class="rounded-xl bg-amber-500 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-amber-200 hover:bg-amber-600 active:scale-95 transition-transform">Simpan Perubahan</button>
             </div>
        </form>
    </Modal>
    
    <!-- Modal Confirm Delete Material -->
    <Modal :show="showDeleteMaterialModal" title="Hapus Materi" variant="danger" max-width="md" @close="closeDeleteMaterialModal">
         <template #description>Konfirmasi penghapusan file.</template>
         <div class="space-y-4">
             <div class="bg-rose-50 p-4 rounded-xl border border-rose-100 text-rose-800 font-bold text-sm">
                 Yakin ingin menghapus materi "{{ materialToDelete?.judul }}"? Tindakan ini tidak dapat dibatalkan.
             </div>
             <div class="flex justify-end gap-3">
                  <button type="button" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-bold text-slate-600" @click="closeDeleteMaterialModal">Batal</button>
                  <button type="button" class="rounded-xl bg-rose-600 px-5 py-2 text-sm font-bold text-white shadow-lg shadow-rose-200 hover:bg-rose-700 active:scale-95" @click="deleteExistingMaterial">Ya, Hapus</button>
             </div>
         </div>
    </Modal>

  </div>
</template>
