<template>
  <div class="space-y-8">
    <Head title="Jadwal & Materi" />

    <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="space-y-1">
          <p class="text-sm font-semibold text-[#78AE4E]">Kelola Jadwal</p>
          <h2 class="text-2xl font-bold text-gray-900">Pantau Jadwal & Materi</h2>
          <p class="text-sm text-gray-500">Unggah jadwal kegiatan belajar mengajar dengan lengkap</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
          <label class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-3 py-2 text-sm text-gray-700">
            <input
              v-model="filters.withTrashed"
              type="checkbox"
              class="h-4 w-4 rounded border-gray-300 text-[#78AE4E] focus:ring-[#78AE4E]"
            />
            <span>Tampilkan jadwal terhapus</span>
          </label>
          <button
            class="rounded-full bg-[#78AE4E] px-5 py-2 text-sm font-semibold text-white shadow"
            :disabled="!hasStudents"
            :class="{ 'opacity-60 cursor-not-allowed': !hasStudents }"
            @click="openAddSchedule"
          >
            + Tambah Jadwal
          </button>
        </div>
      </div>

      <div class="mt-6 grid gap-3 md:grid-cols-3">
        <div class="rounded-2xl border border-gray-100 px-4 py-3">
          <label class="text-xs font-semibold text-gray-500">Cari Jadwal</label>
          <input
            v-model="filters.search"
            type="search"
            placeholder="Cari topik, mapel, atau siswa"
            class="mt-1 w-full border-none bg-transparent text-sm text-gray-800 placeholder-gray-400 focus:outline-none"
          />
        </div>
        <div class="rounded-2xl border border-gray-100 px-4 py-3">
          <label class="text-xs font-semibold text-gray-500">Status</label>
          <select
            v-model="filters.status"
            class="mt-1 w-full border-none bg-transparent text-sm text-gray-800 focus:outline-none"
          >
            <option v-for="status in props.statusOptions" :key="status" :value="status">
              {{ status }}
            </option>
          </select>
        </div>
        <div class="rounded-2xl border border-gray-100 px-4 py-3">
          <p class="text-xs font-semibold text-gray-500">Total Jadwal Ditampilkan</p>
          <p class="mt-1 text-2xl font-bold text-gray-900">{{ jadwalMateri.length }}</p>
        </div>
      </div>
    </section>

    <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
      <div
        v-if="successMessage"
        class="mb-4 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800"
      >
        {{ successMessage }}
      </div>
      <div
        v-if="errorMessage"
        class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700"
      >
        {{ errorMessage }}
      </div>

      <div v-if="isLoadingJadwal" class="space-y-4">
        <div v-for="n in 2" :key="n" class="h-32 animate-pulse rounded-2xl bg-gray-100"></div>
      </div>

      <div v-else class="space-y-6">
        <article
          v-for="schedule in jadwalMateri"
          :key="schedule.id"
          :class="[
            'rounded-3xl border p-5 shadow-sm transition',
            schedule.is_deleted ? 'border-red-100 bg-red-50/70 ring-1 ring-red-100' : 'border-gray-100 bg-[#fdfcf9]',
          ]"
        >
          <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="flex-1 space-y-3">
              <div class="flex flex-wrap items-center gap-3">
                <p class="text-lg font-semibold text-gray-900">{{ formatFullDate(schedule.waktu_mulai) }}</p>
                <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="badgeClass(schedule.status)">
                  {{ schedule.status }}
                </span>
                <span
                  v-if="schedule.is_deleted"
                  class="rounded-full border border-red-200 bg-red-100 px-3 py-1 text-xs font-semibold text-red-700"
                >
                  Terhapus
                </span>
              </div>
              <p class="text-xl font-bold text-gray-900">{{ schedule.topik }}</p>
              <p class="text-sm text-gray-600">
                Peserta:
                <span v-if="schedule.peserta.length">
                  {{ schedule.peserta.map((student) => student.name).join(', ') }}
                </span>
                <span v-else>Belum ditetapkan</span>
              </p>
              <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                <div class="flex items-center gap-2">
                  <ClockIcon class="h-5 w-5 text-[#78AE4E]" />
                  <span>{{ formatTimeRange(schedule.waktu_mulai, schedule.waktu_selesai) }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <MapPinIcon class="h-5 w-5 text-[#78AE4E]" />
                  <span>{{ schedule.lokasi || 'Lokasi belum diisi' }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <UserGroupIcon class="h-5 w-5 text-[#78AE4E]" />
                  <span>
                    {{ schedule.jumlah_peserta ? `${schedule.jumlah_peserta} Peserta` : 'Jumlah peserta belum diisi' }}
                  </span>
                </div>
              </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 self-start">
              <button
                class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="schedule.is_deleted"
                @click="openEditSchedule(schedule)"
              >
                Edit Jadwal
              </button>
              <button
                class="rounded-full border border-[#78AE4E]/60 bg-[#f1f8e9] px-4 py-2 text-sm font-semibold text-[#78AE4E] disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="schedule.is_deleted"
                @click="openEditMateri(schedule)"
              >
                Edit Materi
              </button>
              <button
                v-if="!schedule.is_deleted"
                class="flex items-center gap-2 rounded-full border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="scheduleActionState.deleting[schedule.id]"
                @click="deleteSchedule(schedule)"
              >
                <TrashIcon class="h-5 w-5" />
                <span>{{ scheduleActionState.deleting[schedule.id] ? 'Menghapus...' : 'Hapus' }}</span>
              </button>
              <button
                v-else
                class="flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100 disabled:cursor-not-allowed disabled:opacity-60"
                :disabled="scheduleActionState.restoring[schedule.id]"
                @click="restoreSchedule(schedule)"
              >
                <ArrowPathIcon class="h-5 w-5" />
                <span>{{ scheduleActionState.restoring[schedule.id] ? 'Memulihkan...' : 'Pulihkan' }}</span>
              </button>
            </div>
          </div>

          <div class="mt-5 space-y-3">
            <h3 class="font-semibold text-gray-900">Materi Pembelajaran ({{ schedule.materi.length }} File)</h3>
            <div v-if="!schedule.materi.length" class="rounded-2xl border border-dashed border-gray-200 p-4 text-sm text-gray-500">
              Belum ada materi diunggah.
            </div>
            <div v-else class="space-y-3">
              <button
                v-for="file in schedule.materi"
                :key="file.id"
                type="button"
                class="flex w-full items-center gap-3 rounded-2xl border border-gray-100 bg-white p-3 text-left"
                @click="openMaterialPreview(file)"
              >
                <div class="rounded-xl bg-[#eaf6df] p-2 text-[#78AE4E]">
                  <DocumentArrowUpIcon class="h-5 w-5" />
                </div>
                <div class="flex-1">
                  <p class="font-semibold text-gray-900">{{ file.judul }}</p>
                  <p class="text-sm text-gray-600">{{ file.deskripsi || 'Deskripsi belum diisi' }}</p>
                  <div class="mt-2 h-2 rounded-full bg-gray-100">
                    <div class="h-2 rounded-full bg-[#78AE4E]" style="width: 100%"></div>
                  </div>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="badgeClass(file.status)">
                  {{ file.status || 'Terunggah' }}
                </span>
                <span v-if="file.url" class="text-xs font-semibold text-[#78AE4E]">Lihat</span>
              </button>
            </div>
          </div>
        </article>

        <div v-if="!jadwalMateri.length" class="py-10 text-center text-gray-400">
          Belum ada jadwal yang tercatat.
        </div>
      </div>
    </section>

    <!-- Modal Tambah Jadwal -->
    <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm">
      <div class="w-full max-w-2xl rounded-3xl bg-white shadow-2xl">
        <header class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
          <div>
            <p class="text-sm font-semibold text-[#78AE4E]">Tambah Jadwal</p>
            <p class="text-xl font-bold text-gray-900">Isi data jadwal kegiatan belajar mengajar dengan lengkap</p>
          </div>
          <button class="text-xl text-gray-400 transition hover:text-gray-600" @click="closeAddSchedule">×</button>
        </header>

        <form class="max-h-[75vh] space-y-4 overflow-y-auto px-6 py-6" @submit.prevent="submitAddSchedule">
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="text-sm font-semibold text-gray-700">Untuk Siswa</label>
              <div class="mt-1 rounded-2xl border border-gray-200 p-3">
                <p v-if="!hasStudents" class="text-xs font-semibold text-red-600">Tambahkan data siswa terlebih dahulu.</p>
                <div v-else class="flex flex-wrap gap-2">
                  <label
                    v-for="student in props.students"
                    :key="student.id"
                    class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1 text-sm text-gray-700"
                  >
                    <input
                      v-model="addForm.student_ids"
                      :value="student.id"
                      type="checkbox"
                      class="h-4 w-4 rounded border-gray-300 text-[#78AE4E] focus:ring-[#78AE4E]"
                    />
                    <span>{{ student.name }}</span>
                  </label>
                </div>
                <p class="mt-2 text-xs text-gray-500">Pilih satu atau beberapa siswa sesuai kebutuhan.</p>
              </div>
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700">Mata Pelajaran</label>
              <input
                v-model="addForm.subject"
                required
                type="text"
                placeholder="Masukkan nama Mata Pelajaran - Kegiatan"
                class="mt-1 w-full rounded-2xl border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#78AE4E]"
              />
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700">Topik Pembelajaran</label>
              <input
                v-model="addForm.topic"
                required
                type="text"
                placeholder="Contoh: Materi Trigonometri"
                class="mt-1 w-full rounded-2xl border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#78AE4E]"
              />
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700">Hari, Tanggal</label>
              <input
                v-model="addForm.tanggal"
                required
                type="date"
                :min="minScheduleDate"
                class="mt-1 w-full rounded-2xl border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#78AE4E]"
              />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="text-sm font-semibold text-gray-700">Jam Mulai</label>
                <input
                  v-model="addForm.jam_mulai"
                  required
                  type="time"
                  class="mt-1 w-full rounded-2xl border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#78AE4E]"
                />
              </div>
              <div>
                <label class="text-sm font-semibold text-gray-700">Jam Selesai</label>
                <input
                  v-model="addForm.jam_selesai"
                  required
                  type="time"
                  class="mt-1 w-full rounded-2xl border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#78AE4E]"
                />
                <p v-if="addTimeError" class="mt-2 text-xs font-semibold text-red-600">{{ addTimeError }}</p>
              </div>
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700">Ruangan/Lokasi</label>
              <input
                v-model="addForm.location"
                type="text"
                placeholder="Isikan lokasi kegiatan belajar mengajar"
                class="mt-1 w-full rounded-2xl border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#78AE4E]"
              />
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700">Jumlah Peserta Didik</label>
              <input
                v-model="addForm.max_participants"
                type="number"
                min="1"
                readonly
                class="mt-1 w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:outline-none"
              />
            </div>
          </div>

          <div>
            <label class="text-sm font-semibold text-gray-700">Deskripsi Singkat Materi</label>
            <textarea
              v-model="addForm.description"
              rows="3"
              placeholder="Tuliskan deskripsi materi yang diajarkan"
              class="mt-1 w-full rounded-2xl border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#78AE4E]"
            ></textarea>
          </div>

          <div class="space-y-2">
            <label class="text-sm font-semibold text-gray-700">Tambahkan Materi</label>
            <p class="text-xs text-gray-500">Status materi akan otomatis menjadi "Terunggah" ketika proses berhasil.</p>
            <label
              :for="addMaterialInputId"
              class="block cursor-pointer rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 px-4 py-6 text-center hover:border-[#78AE4E]"
              role="button"
              tabindex="0"
            >
              <p class="text-sm text-gray-600">Klik untuk upload atau drag and drop</p>
              <p class="text-xs text-gray-400">PNG, MP4, PPT, DOCX, PDF</p>
            </label>
            <input
              :id="addMaterialInputId"
              ref="materialInput"
              type="file"
              class="sr-only"
              multiple
              @change="handleMaterialFiles($event, addForm)"
            />
            <div v-if="addForm.materi_uploads.length" class="space-y-2">
              <div
                v-for="file in addForm.materi_uploads"
                :key="file.name"
                class="flex items-center justify-between rounded-xl border border-gray-100 bg-white px-3 py-2 text-sm"
              >
                <span class="text-gray-700">{{ file.name }}</span>
                <span class="text-xs text-gray-500">{{ (file.size / 1024 / 1024).toFixed(2) }} MB</span>
              </div>
            </div>
            <div v-if="uploadProgress > 0" class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
              <div class="h-2 bg-[#78AE4E]" :style="{ width: `${uploadProgress}%` }"></div>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <button type="button" class="rounded-2xl border border-gray-200 px-4 py-2 font-semibold text-gray-700" @click="closeAddSchedule">
              Batal
            </button>
            <button type="submit" class="rounded-2xl bg-[#78AE4E] px-5 py-2 font-semibold text-white shadow">
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Edit Jadwal -->
    <div v-if="showEditScheduleModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm">
      <div class="w-full max-w-xl rounded-3xl bg-white shadow-2xl">
        <header class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
          <div>
            <p class="text-sm font-semibold text-[#78AE4E]">Edit Jadwal</p>
            <p class="text-xl font-bold text-gray-900">Perbarui jadwal sesuai kebutuhan</p>
            <div class="mt-1 flex items-center gap-2 text-sm text-gray-600">
              <span>Status saat ini:</span>
              <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="badgeClass(editForm.status)">
                {{ editForm.status || 'Akan Datang' }}
              </span>
            </div>
          </div>
          <button class="text-xl text-gray-400 transition hover:text-gray-600" @click="closeEditSchedule">×</button>
        </header>

        <form class="max-h-[75vh] space-y-4 overflow-y-auto px-6 py-6" @submit.prevent="submitEditSchedule">
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="text-sm font-semibold text-gray-700">Untuk Siswa</label>
              <div class="mt-1 rounded-2xl border border-gray-200 p-3">
                <p v-if="!hasStudents" class="text-xs font-semibold text-red-600">Tambahkan data siswa terlebih dahulu.</p>
                <div v-else class="flex flex-wrap gap-2">
                  <label
                    v-for="student in props.students"
                    :key="student.id"
                    class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-3 py-1 text-sm text-gray-700"
                  >
                    <input
                      v-model="editForm.student_ids"
                      :value="student.id"
                      type="checkbox"
                      class="h-4 w-4 rounded border-gray-300 text-[#78AE4E] focus:ring-[#78AE4E]"
                    />
                    <span>{{ student.name }}</span>
                  </label>
                </div>
                <p class="mt-2 text-xs text-gray-500">Pilih satu atau beberapa siswa sesuai kebutuhan.</p>
              </div>
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700">Mata Pelajaran</label>
              <input
                v-model="editForm.subject"
                required
                type="text"
                class="mt-1 w-full rounded-2xl border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#78AE4E]"
              />
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700">Topik Pembelajaran</label>
              <input
                v-model="editForm.topic"
                required
                type="text"
                class="mt-1 w-full rounded-2xl border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#78AE4E]"
              />
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700">Hari, Tanggal</label>
              <input
                v-model="editForm.tanggal"
                required
                type="date"
                :min="minScheduleDate"
                class="mt-1 w-full rounded-2xl border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#78AE4E]"
              />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="text-sm font-semibold text-gray-700">Jam Mulai</label>
                <input
                  v-model="editForm.jam_mulai"
                  required
                  type="time"
                  class="mt-1 w-full rounded-2xl border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#78AE4E]"
                />
              </div>
              <div>
                <label class="text-sm font-semibold text-gray-700">Jam Selesai</label>
                <input
                  v-model="editForm.jam_selesai"
                  required
                  type="time"
                  class="mt-1 w-full rounded-2xl border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#78AE4E]"
                />
                <p v-if="editTimeError" class="mt-2 text-xs font-semibold text-red-600">{{ editTimeError }}</p>
              </div>
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700">Ruangan/Lokasi</label>
              <input
                v-model="editForm.location"
                type="text"
                class="mt-1 w-full rounded-2xl border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#78AE4E]"
              />
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700">Jumlah Peserta Didik</label>
              <input
                v-model="editForm.max_participants"
                type="number"
                min="1"
                readonly
                class="mt-1 w-full rounded-2xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:outline-none"
              />
            </div>
          </div>
          <div>
            <label class="text-sm font-semibold text-gray-700">Deskripsi Singkat Materi</label>
            <textarea
              v-model="editForm.description"
              rows="3"
              class="mt-1 w-full rounded-2xl border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#78AE4E]"
            ></textarea>
          </div>

          <div class="flex justify-end gap-3">
            <button type="button" class="rounded-2xl border border-gray-200 px-4 py-2 font-semibold text-gray-700" @click="closeEditSchedule">
              Batal
            </button>
            <button type="submit" class="rounded-2xl bg-[#78AE4E] px-5 py-2 font-semibold text-white shadow">
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Edit Materi -->
    <div v-if="showEditMateriModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm">
      <div class="w-full max-w-2xl rounded-3xl bg-white shadow-2xl">
        <header class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
          <div>
            <p class="text-sm font-semibold text-[#78AE4E]">Edit Materi</p>
            <p class="text-xl font-bold text-gray-900">Kelola materi yang sudah diunggah</p>
          </div>
          <button class="text-xl text-gray-400 transition hover:text-gray-600" @click="closeEditMateri">×</button>
        </header>

        <form class="max-h-[75vh] space-y-4 overflow-y-auto px-6 py-6" @submit.prevent="submitEditMateri">
          <div v-for="material in materiForm.existing" :key="material.id" class="space-y-3 rounded-2xl border border-gray-100 p-4">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-xs font-semibold uppercase text-gray-400">Materi Terunggah</p>
                <p class="text-base font-bold text-gray-900">{{ material.judul || 'Tanpa judul' }}</p>
              </div>
              <button
                type="button"
                class="flex items-center gap-2 rounded-full border border-red-200 bg-red-50 px-3 py-1 text-xs font-semibold text-red-600 hover:bg-red-100 disabled:opacity-60"
                :disabled="material.deleting"
                @click="confirmDeleteMaterial(material)"
              >
                <TrashIcon class="h-4 w-4" />
                <span>{{ material.deleting ? 'Menghapus...' : 'Hapus' }}</span>
              </button>
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700">Judul Materi</label>
              <input
                v-model="material.judul"
                required
                type="text"
                class="mt-1 w-full rounded-2xl border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#78AE4E]"
              />
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700">Deskripsi Singkat Materi</label>
              <textarea
                v-model="material.deskripsi"
                rows="2"
                class="mt-1 w-full rounded-2xl border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#78AE4E]"
              ></textarea>
            </div>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
              <div class="flex items-center gap-2 text-sm text-gray-600">
                <span class="rounded-full bg-[#eaf6df] px-3 py-1 text-xs font-semibold text-[#78AE4E]">
                  Status: {{ material.status || 'Terunggah' }}
                </span>
                <span class="text-xs text-gray-500">Otomatis diperbarui setelah upload berhasil</span>
              </div>
              <div>
                <label class="text-sm font-semibold text-gray-700">Ganti File (opsional)</label>
                <input type="file" class="mt-1 w-full text-sm" @change="onReplaceFile($event, material)" />
                <p v-if="material.newFile" class="text-xs text-emerald-600">File baru: {{ material.newFile.name }}</p>
              </div>
            </div>
          </div>

          <div class="space-y-3">
            <p class="text-sm font-semibold text-gray-700">Tambahkan Materi Baru</p>
            <p class="text-xs text-gray-500">Status materi baru akan otomatis menjadi "Terunggah" ketika upload sukses.</p>
            <label
              :for="editMaterialInputId"
              class="block cursor-pointer rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50 px-4 py-6 text-center hover:border-[#78AE4E]"
              role="button"
              tabindex="0"
            >
              <p class="text-sm text-gray-600">Klik untuk upload atau drag and drop</p>
              <p class="text-xs text-gray-400">PNG, MP4, PPT, DOCX, PDF</p>
            </label>
            <input
              :id="editMaterialInputId"
              ref="newMaterialInput"
              type="file"
              class="sr-only"
              multiple
              @change="handleMaterialFiles($event, materiForm)"
            />
            <div v-if="materiForm.materi_uploads.length" class="space-y-2">
              <div
                v-for="file in materiForm.materi_uploads"
                :key="file.name"
                class="flex items-center justify-between rounded-xl border border-gray-100 bg-white px-3 py-2 text-sm"
              >
                <span class="text-gray-700">{{ file.name }}</span>
                <span class="text-xs text-gray-500">{{ (file.size / 1024 / 1024).toFixed(2) }} MB</span>
              </div>
            </div>
            <div>
              <label class="text-sm font-semibold text-gray-700">Deskripsi Materi Baru</label>
              <textarea
                v-model="materiForm.materi_deskripsi"
                rows="2"
                class="mt-1 w-full rounded-2xl border border-gray-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#78AE4E]"
              ></textarea>
            </div>
            <div v-if="materialUploadProgress > 0" class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
              <div class="h-2 bg-[#78AE4E]" :style="{ width: `${materialUploadProgress}%` }"></div>
            </div>
          </div>

          <div class="flex justify-end gap-3">
            <button type="button" class="rounded-2xl border border-gray-200 px-4 py-2 font-semibold text-gray-700" @click="closeEditMateri">
              Batal
            </button>
            <button type="submit" class="rounded-2xl bg-[#78AE4E] px-5 py-2 font-semibold text-white shadow">
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>

    <div
      v-if="showDeleteMaterialModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
    >
      <div class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl">
        <header class="bg-red-600 px-6 py-4 text-white">
          <p class="text-sm font-semibold uppercase tracking-wide">Hapus Materi</p>
          <p class="text-lg font-bold">Konfirmasi Penghapusan</p>
        </header>
        <div class="space-y-5 px-6 py-6">
          <div class="rounded-2xl border border-red-100 bg-red-50 p-4 text-center text-sm font-semibold text-red-700">
            Apakah anda yakin untuk menghapus materi ini?
          </div>
          <div class="space-y-2 rounded-2xl border border-gray-200 p-4 text-sm text-gray-700">
            <p>
              <span class="font-semibold">Judul materi:</span>
              {{ materialToDelete ? materialToDelete.judul || 'Tanpa judul' : '-' }}
            </p>
            <p>
              <span class="font-semibold">Tanggal jadwal:</span>
              {{ formatFullDate((materialToDelete && materialToDelete.scheduleDate) || materiForm.scheduleDate) }}
            </p>
          </div>
          <div class="flex items-center justify-end gap-3">
            <button
              type="button"
              class="rounded-2xl border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700"
              @click="closeDeleteMaterialModal"
            >
              Batal
            </button>
            <button
              type="button"
              class="rounded-2xl bg-red-600 px-5 py-2 text-sm font-semibold text-white shadow"
              :disabled="materialToDelete && materialToDelete.deleting"
              @click="deleteExistingMaterial"
            >
              {{ materialToDelete && materialToDelete.deleting ? 'Menghapus...' : 'Hapus' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import GuruLayout from '@/Layouts/GuruLayout.vue'
import axios from 'axios'
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue'
import { route } from 'ziggy-js'
import {
  ArrowPathIcon,
  ClockIcon,
  DocumentArrowUpIcon,
  MapPinIcon,
  TrashIcon,
  UserGroupIcon,
} from '@heroicons/vue/24/outline'

defineOptions({ layout: GuruLayout })

const props = defineProps({
  statusOptions: {
    type: Array,
    default: () => ['Semua', 'Akan Datang', 'Berlangsung', 'Selesai', 'Dibatalkan'],
  },
  students: {
    type: Array,
    default: () => [],
  },
})

const hasStudents = computed(() => props.students.length > 0)

const jadwalMateri = ref([])
const isLoadingJadwal = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const uploadProgress = ref(0)
const materialUploadProgress = ref(0)
const minScheduleDate = new Date().toISOString().slice(0, 10)

const filters = reactive({
  status: props.statusOptions?.[0] ?? 'Semua',
  search: '',
  withTrashed: false,
})

const addTimeError = ref('')
const editTimeError = ref('')

const showAddModal = ref(false)
const showEditScheduleModal = ref(false)
const showEditMateriModal = ref(false)
const showDeleteMaterialModal = ref(false)

const scheduleActionState = reactive({
  deleting: {},
  restoring: {},
})

const addForm = reactive({
  student_ids: [],
  subject: '',
  topic: '',
  tanggal: '',
  jam_mulai: '',
  jam_selesai: '',
  location: '',
  max_participants: '',
  description: '',
  materi_uploads: [],
})

const editForm = reactive({
  id: null,
  student_ids: [],
  subject: '',
  topic: '',
  tanggal: '',
  jam_mulai: '',
  jam_selesai: '',
  location: '',
  max_participants: '',
  description: '',
  status: 'Akan Datang',
})

const materiForm = reactive({
  scheduleId: null,
  scheduleDate: null,
  existing: [],
  materi_uploads: [],
  materi_deskripsi: '',
})

const generateUploadId = (prefix) => `${prefix}-${Math.random().toString(36).slice(2, 8)}`
const addMaterialInputId = generateUploadId('material-upload-add')
const editMaterialInputId = generateUploadId('material-upload-edit')

const materialInput = ref(null)
const newMaterialInput = ref(null)
const materialToDelete = ref(null)

const badgeClass = (status) => {
  const map = {
    'Akan Datang': 'bg-blue-50 text-blue-700',
    Berlangsung: 'bg-emerald-50 text-emerald-700',
    Selesai: 'bg-gray-100 text-gray-700',
    Dibatalkan: 'bg-red-50 text-red-700',
    Terunggah: 'bg-[#eaf6df] text-[#78AE4E]',
  }
  return map[status] ?? 'bg-gray-100 text-gray-700'
}

const formatFullDate = (value) => {
  if (!value) return '-'
  return new Date(value).toLocaleDateString('id-ID', {
    weekday: 'long',
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  })
}

const formatTimeRange = (start, end) => {
  if (!start) return 'Waktu belum ditentukan'
  const timeFormatter = new Intl.DateTimeFormat('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
  })
  const startLabel = timeFormatter.format(new Date(start))
  const endLabel = end ? timeFormatter.format(new Date(end)) : '—'
  return `${startLabel} - ${endLabel}`
}

const computeScheduleStatus = (schedule) => {
  const specialStatuses = ['Dibatalkan']
  if (schedule.status_badge && specialStatuses.includes(schedule.status_badge)) {
    return schedule.status_badge
  }

  const now = new Date()
  const start = schedule.start_time ? new Date(schedule.start_time) : null
  const end = schedule.end_time ? new Date(schedule.end_time) : start

  if (!start) {
    return schedule.status_badge ?? 'Akan Datang'
  }

  if (now < start) return 'Akan Datang'
  if (end && now >= start && now <= end) return 'Berlangsung'
  return 'Selesai'
}

const mapSchedule = (schedule) => ({
  id: schedule.id,
  status: computeScheduleStatus(schedule),
  status_color: schedule.status_color,
  is_deleted: Boolean(schedule.deleted_at),
  waktu_mulai: schedule.start_time,
  waktu_selesai: schedule.end_time,
  siswa: schedule.students?.[0] ?? schedule.student,
  peserta: schedule.students || [],
  topik: schedule.subject || schedule.topic,
  topik_pembelajaran: schedule.topic,
  deskripsi: schedule.description,
  lokasi: schedule.location,
  jumlah_peserta: schedule.max_participants ?? schedule.students?.length ?? 0,
  materi: (schedule.materials || []).map((material) => ({
    id: material.id,
    judul: material.title,
    deskripsi: material.description,
    status: material.status ?? 'Terunggah',
    url: material.download_url,
  })),
  raw: schedule,
})

const setSuccessMessage = (message) => {
  successMessage.value = message
  if (message) {
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
  }
}

const setErrorMessage = (message) => {
  errorMessage.value = message
  if (message) {
    setTimeout(() => {
      errorMessage.value = ''
    }, 4000)
  }
}

const handleApiError = (error) => {
  if (error.response?.data?.message) {
    setErrorMessage(error.response.data.message)
  } else {
    setErrorMessage('Terjadi kesalahan, silakan coba lagi.')
  }
}

const fetchSchedules = async () => {
  isLoadingJadwal.value = true
  try {
    const { data } = await axios.get(route('guru.api.schedules.index'), {
      params: {
        status: filters.status,
        search: filters.search,
        with_trashed: filters.withTrashed,
        per_page: 50,
      },
    })
    jadwalMateri.value = data.data.map(mapSchedule)
  } catch (error) {
    handleApiError(error)
  } finally {
    isLoadingJadwal.value = false
  }
}

const resetAddForm = () => {
  Object.assign(addForm, {
    student_ids: [],
    subject: '',
    topic: '',
    tanggal: '',
    jam_mulai: '',
    jam_selesai: '',
    location: '',
    max_participants: '',
    description: '',
    materi_uploads: [],
  })
  addTimeError.value = ''
  uploadProgress.value = 0
}

const resetEditForm = () => {
  Object.assign(editForm, {
    id: null,
    student_ids: [],
    subject: '',
    topic: '',
    tanggal: '',
    jam_mulai: '',
    jam_selesai: '',
    location: '',
    max_participants: '',
    description: '',
    status: 'Akan Datang',
  })
  editTimeError.value = ''
}

const resetMateriForm = () => {
  Object.assign(materiForm, {
    scheduleId: null,
    scheduleDate: null,
    existing: [],
    materi_uploads: [],
    materi_deskripsi: '',
  })
  materialUploadProgress.value = 0
}

const combineDateTime = (date, time) => {
  if (!date || !time) return null
  const composed = new Date(`${date}T${time}`)
  return composed.toISOString()
}

const validateTimeRange = (start, end) => {
  if (!start || !end) return ''
  return start >= end ? 'Jam selesai harus setelah jam mulai.' : ''
}

const buildSchedulePayload = (form) => ({
  student_ids: form.student_ids,
  subject: form.subject,
  topic: form.topic,
  learning_focus: form.topic,
  description: form.description,
  start_time: combineDateTime(form.tanggal, form.jam_mulai),
  end_time: combineDateTime(form.tanggal, form.jam_selesai),
  location: form.location,
  max_participants: form.max_participants || form.student_ids.length || null,
})

const uploadMaterialsForSchedule = async (scheduleId, files, description, progressRef) => {
  if (!files.length) return
  progressRef.value = 0
  for (const file of files) {
    const formData = new FormData()
    formData.append('title', file.name)
    formData.append('description', description || '')
    formData.append('status', 'Terunggah')
    formData.append('file', file)
    await axios.post(route('guru.api.materials.store', scheduleId), formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      onUploadProgress: (event) => {
        if (event.total) {
          progressRef.value = Math.round((event.loaded / event.total) * 100)
        }
      },
    })
  }
  progressRef.value = 0
}

const openAddSchedule = () => {
  resetAddForm()
  showAddModal.value = true
}

const closeAddSchedule = () => {
  showAddModal.value = false
  resetAddForm()
}

const submitAddSchedule = async () => {
  if (!addForm.student_ids.length) {
    setErrorMessage('Pilih minimal satu siswa terlebih dahulu.')
    return
  }
  addTimeError.value = validateTimeRange(addForm.jam_mulai, addForm.jam_selesai)
  if (addTimeError.value) return
  try {
    const payload = buildSchedulePayload(addForm)
    const { data } = await axios.post(route('guru.api.schedules.store'), payload)
    await uploadMaterialsForSchedule(data.id, addForm.materi_uploads, addForm.description, uploadProgress)
    setSuccessMessage('Jadwal berhasil ditambahkan.')
    closeAddSchedule()
    await fetchSchedules()
  } catch (error) {
    handleApiError(error)
  }
}

const openEditSchedule = (schedule) => {
  const raw = schedule.raw
  const { date, time } = splitDateTime(raw.start_time)
  const end = splitDateTime(raw.end_time || raw.start_time)
  Object.assign(editForm, {
    id: raw.id,
    student_ids: raw.students?.map((student) => student.id) ?? (raw.student_id ? [raw.student_id] : []),
    subject: raw.subject,
    topic: raw.topic,
    tanggal: date,
    jam_mulai: time,
    jam_selesai: end.time,
    location: raw.location,
    max_participants: raw.max_participants,
    description: raw.description,
    status: raw.status_badge ?? 'Akan Datang',
  })
  editTimeError.value = ''
  showEditScheduleModal.value = true
}

const closeEditSchedule = () => {
  showEditScheduleModal.value = false
  resetEditForm()
}

const submitEditSchedule = async () => {
  if (!editForm.student_ids.length) {
    setErrorMessage('Pilih minimal satu siswa terlebih dahulu.')
    return
  }
  editTimeError.value = validateTimeRange(editForm.jam_mulai, editForm.jam_selesai)
  if (editTimeError.value) return
  try {
    const payload = buildSchedulePayload(editForm)
    await axios.put(route('guru.api.schedules.update', editForm.id), payload)
    setSuccessMessage('Jadwal berhasil diperbarui.')
    closeEditSchedule()
    await fetchSchedules()
  } catch (error) {
    handleApiError(error)
  }
}

const openEditMateri = (schedule) => {
  resetMateriForm()
  materiForm.scheduleId = schedule.id
  materiForm.scheduleDate = schedule.waktu_mulai
  materiForm.existing = schedule.materi.map((item) => ({
    id: item.id,
    judul: item.judul,
    deskripsi: item.deskripsi,
    status: item.status,
    newFile: null,
    deleting: false,
    scheduleDate: schedule.waktu_mulai,
  }))
  showEditMateriModal.value = true
}

const closeEditMateri = () => {
  showEditMateriModal.value = false
  resetMateriForm()
}

const submitEditMateri = async () => {
  try {
    for (const material of materiForm.existing) {
      const formData = new FormData()
      formData.append('title', material.judul)
      formData.append('description', material.deskripsi || '')
      formData.append('status', material.status || 'Terunggah')
      if (material.newFile) {
        formData.append('file', material.newFile)
      }
      formData.append('_method', 'PUT')
      await axios.post(route('guru.api.materials.update', material.id), formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    }

    await uploadMaterialsForSchedule(
      materiForm.scheduleId,
      materiForm.materi_uploads,
      materiForm.materi_deskripsi,
      materialUploadProgress
    )

    setSuccessMessage('Materi berhasil diperbarui.')
    closeEditMateri()
    await fetchSchedules()
  } catch (error) {
    handleApiError(error)
  }
}
const confirmDeleteMaterial = (material) => {
  materialToDelete.value = material
  showDeleteMaterialModal.value = true
}

const closeDeleteMaterialModal = () => {
  showDeleteMaterialModal.value = false
  materialToDelete.value = null
}

const deleteExistingMaterial = async () => {
  const material = materialToDelete.value
  if (!material?.id) return
  material.deleting = true
  try {
    await axios.delete(route('guru.api.materials.destroy', material.id))
    materiForm.existing = materiForm.existing.filter((item) => item.id !== material.id)
    setSuccessMessage('Materi berhasil dihapus.')
    await fetchSchedules()
    closeDeleteMaterialModal()
  } catch (error) {
    handleApiError(error)
  } finally {
    material.deleting = false
  }
}

const handleMaterialFiles = (event, target) => {
  const files = Array.from(event.target.files || [])
  target.materi_uploads.push(...files)
  event.target.value = ''
}

const onReplaceFile = (event, material) => {
  const [file] = event.target.files || []
  if (file) {
    material.newFile = file
  }
  event.target.value = ''
}

const deleteSchedule = async (schedule) => {
  if (!confirm('Hapus jadwal ini?')) return
  scheduleActionState.deleting[schedule.id] = true
  try {
    await axios.delete(route('guru.api.schedules.destroy', schedule.id))
    setSuccessMessage('Jadwal dihapus (dapat dipulihkan).')
    await fetchSchedules()
  } catch (error) {
    handleApiError(error)
  } finally {
    scheduleActionState.deleting[schedule.id] = false
  }
}

const restoreSchedule = async (schedule) => {
  scheduleActionState.restoring[schedule.id] = true
  try {
    await axios.post(route('guru.api.schedules.restore', schedule.id))
    setSuccessMessage('Jadwal berhasil dipulihkan.')
    await fetchSchedules()
  } catch (error) {
    handleApiError(error)
  } finally {
    scheduleActionState.restoring[schedule.id] = false
  }
}

const openMaterialPreview = (file) => {
  if (file.url) {
    window.open(file.url, '_blank')
  }
}

const splitDateTime = (value) => {
  if (!value) return { date: '', time: '' }
  const date = new Date(value)
  const iso = new Date(date.getTime() - date.getTimezoneOffset() * 60000)
    .toISOString()
    .slice(0, 16)
  const [d, t] = iso.split('T')
  return { date: d, time: t }
}

watch(
  () => addForm.student_ids.length,
  (count) => {
    addForm.max_participants = count ? count : ''
  },
  { immediate: true }
)

watch(
  () => editForm.student_ids.length,
  (count) => {
    editForm.max_participants = count ? count : ''
  },
  { immediate: true }
)

let searchDebounce

watch(
  () => [filters.status, filters.withTrashed],
  () => {
    fetchSchedules()
  }
)

watch(
  () => filters.search,
  () => {
    clearTimeout(searchDebounce)
    searchDebounce = setTimeout(() => {
      fetchSchedules()
    }, 400)
  }
)

onBeforeUnmount(() => {
  clearTimeout(searchDebounce)
})

fetchSchedules()
</script>
