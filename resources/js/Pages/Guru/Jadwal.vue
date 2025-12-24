<script setup>
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';
import { route } from 'ziggy-js';
import Modal from '@/Components/Modal.vue';
import {
    ClockIcon, PlusIcon, MagnifyingGlassIcon, FunnelIcon, CalendarIcon,
    CheckCircleIcon, ExclamationTriangleIcon, MapPinIcon, ClipboardDocumentCheckIcon,
    TrashIcon, ArrowPathIcon, DocumentArrowUpIcon, DocumentTextIcon
} from '@heroicons/vue/24/outline';
import GuruLayout from '@/Layouts/GuruLayout.vue';

defineOptions({
    layout: GuruLayout
});

const debounce = (fn, delay) => {
    let timeoutId;
    return (...args) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn(...args), delay);
    };
};

const props = defineProps({
    filters: Object,
    statusOptions: {
        type: Array,
        default: () => ['Semua', 'Akan Datang', 'Berlangsung', 'Selesai']
    },
    students: {
        type: Array,
        default: () => []
    }
});

const MAX_MATERIAL_SIZE = 10 * 1024 * 1024; // 10 MB
const ALLOWED_MATERIAL_TYPES = ['application/pdf', 'image/png', 'image/jpeg'];

const filters = reactive({
    search: '',
    status: props.filters?.status || 'Semua',
    withTrashed: false
});

const schedulesList = ref([]);
const isLoadingJadwal = ref(false);
const successMessage = ref(null);
const errorMessage = ref(null);
const addMaterialError = ref('');
const editMaterialError = ref('');
const currentTime = ref(new Date());

const showAddModal = ref(false);
const showEditScheduleModal = ref(false);
const showEditMateriModal = ref(false);
const showDeleteMaterialModal = ref(false);
const materialToDelete = ref(null);

const addTimeError = ref('');
const editTimeError = ref('');
const addMaterialInputId = 'add-material-input';
const editMaterialInputId = 'edit-material-input';

const scheduleActionState = reactive({
    deleting: {},
    restoring: {},
    submitting: false
});

const addForm = reactive({
    student_ids: [],
    subject: '',
    topic: '',
    tanggal: '',
    jam_mulai: '',
    jam_selesai: '',
    location: '',
    description: '',
    materi_uploads: []
});

const editForm = reactive({
    id: null,
    student_ids: [],
    subject: '',
    topic: '',
    tanggal: '',
    jam_mulai: '',
    jam_selesai: '',
    location: '',
    description: ''
});

const materiForm = reactive({
    schedule_id: null,
    existing: [],
    materi_uploads: []
});

const hasStudents = computed(() => props.students && props.students.length > 0);
const minScheduleDate = computed(() => new Date().toISOString().split('T')[0]);

// Real-time status calculation - updates every second
let timeUpdateInterval = null;

// Function exposed to template for real-time status computation
const computeScheduleStatus = (schedule) => {
    const now = currentTime.value;
    const startTime = schedule.waktu_mulai || schedule.raw?.start_time;
    const endTime = schedule.waktu_selesai || schedule.raw?.end_time;
    
    const start = new Date(startTime);
    const end = new Date(endTime);
    
    if (isNaN(start.getTime())) return 'Akan Datang';
    
    if (now < start) return 'Akan Datang';
    if (now >= start && now <= end) return 'Berlangsung';
    return 'Selesai';
};

const mapSchedule = (schedule) => ({
    id: schedule.id,
    status: computeScheduleStatus(schedule),
    is_deleted: !!schedule.deleted_at,
    waktu_mulai: schedule.start_time,
    waktu_selesai: schedule.end_time,
    topik: schedule.topic || schedule.subject || 'Tanpa Topik',
    subject: schedule.subject || '',
    lokasi: schedule.location || 'Online/Tidak ditentukan',
    peserta: schedule.students || [],
    materi: (schedule.materials || []).map(m => ({
        id: m.id,
        judul: m.title,
        deskripsi: m.description,
        status: m.status || 'Terunggah',
        url: m.download_url
    })),
    raw: schedule
});

const fetchSchedules = async () => {
    isLoadingJadwal.value = true;
    errorMessage.value = null;
    try {
        const response = await axios.get(route('guru.api.schedules.index'), {
            params: {
                search: filters.search,
                status: filters.status === 'Semua' ? null : filters.status,
                only_trashed: filters.withTrashed ? 1 : 0
            }
        });
        const data = response.data.data || response.data;
        schedulesList.value = Array.isArray(data) ? data.map(mapSchedule) : [];
    } catch (error) {
        console.error('Error fetching schedules:', error);
        errorMessage.value = 'Gagal memuat jadwal.';
        schedulesList.value = [];
    } finally {
        isLoadingJadwal.value = false;
    }
};

const setSuccessMessage = (msg) => {
    successMessage.value = msg;
    setTimeout(() => { successMessage.value = null; }, 3000);
};

onMounted(() => {
    fetchSchedules();
    // Update currentTime every second for real-time status
    timeUpdateInterval = setInterval(() => {
        currentTime.value = new Date();
    }, 1000);
});

onBeforeUnmount(() => {
    if (timeUpdateInterval) {
        clearInterval(timeUpdateInterval);
    }
});

watch(filters, debounce(() => {
    fetchSchedules();
}, 500), { deep: true });

const badgeClass = (status) => {
    const classes = {
        'Akan Datang': 'bg-amber-100 text-amber-700 border-amber-200',
        'Berlangsung': 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'Selesai': 'bg-slate-100 text-slate-700 border-slate-200'
    };
    return classes[status] || 'bg-gray-100 text-gray-700';
};

const formatFullDate = (date) => {
    if (!date) return '-';
    try {
        return new Date(date).toLocaleDateString('id-ID', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
    } catch (e) { return '-'; }
};

const formatTimeRange = (start, end) => {
    if (!start) return '-';
    try {
        const fmt = (d) => {
            if (!d) return '-';
            const date = d.includes('T') || d.includes('-') ? new Date(d) : new Date(`1970-01-01T${d}`);
            if (isNaN(date.getTime())) return '-';
            return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        };
        return `${fmt(start)} - ${end ? fmt(end) : 'Selesai'}`;
    } catch (e) { return '-'; }
};

const combineDateTime = (date, time) => {
    if (!date || !time) return null;
    return `${date} ${time}:00`;
};

const resetAddForm = () => {
    Object.assign(addForm, {
        student_ids: [],
        subject: '',
        topic: '',
        tanggal: '',
        jam_mulai: '',
        jam_selesai: '',
        location: '',
        description: '',
        materi_uploads: []
    });
    addTimeError.value = '';
    addMaterialError.value = '';
};

const openAddSchedule = () => {
    resetAddForm();
    showAddModal.value = true;
};
const closeAddSchedule = () => showAddModal.value = false;

const submitAddSchedule = async () => {
    if (!addForm.student_ids.length) {
        addTimeError.value = 'Pilih minimal satu siswa.';
        return;
    }
    if (addForm.jam_mulai >= addForm.jam_selesai) {
        addTimeError.value = 'Jam selesai harus lebih besar dari jam mulai.';
        return;
    }
    
    scheduleActionState.submitting = true;
    addTimeError.value = '';
    
    let createdScheduleId = null;
    try {
        const payload = {
            student_ids: addForm.student_ids,
            subject: addForm.subject,
            topic: addForm.topic,
            description: addForm.description,
            start_time: combineDateTime(addForm.tanggal, addForm.jam_mulai),
            end_time: combineDateTime(addForm.tanggal, addForm.jam_selesai),
            location: addForm.location || 'Online',
        };

        const { data } = await axios.post(route('guru.api.schedules.store'), payload);
        createdScheduleId = data.id;

        if (addForm.materi_uploads.length && createdScheduleId) {
            for (const file of addForm.materi_uploads) {
                const formData = new FormData();
                formData.append('title', file.name);
                formData.append('file', file);
                await axios.post(route('guru.api.materials.store', createdScheduleId), formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
            }
        }

        setSuccessMessage('Jadwal berhasil dibuat!');
        closeAddSchedule();
        fetchSchedules();
    } catch (error) {
        console.error('Error creating schedule:', error);

        // Roll back schedule if material upload failed after creation
        if (createdScheduleId) {
            try {
                await axios.delete(route('guru.api.schedules.destroy', createdScheduleId));
            } catch (rollbackError) {
                console.error('Rollback schedule failed:', rollbackError);
            }
        }

        addTimeError.value = error.response?.data?.message || 'Gagal menyimpan jadwal atau mengunggah materi (maks 10MB, PDF/PNG/JPEG).';
    } finally {
        scheduleActionState.submitting = false;
    }
};

const openEditSchedule = (schedule) => {
    const raw = schedule.raw || schedule;
    const start = raw.start_time ? new Date(raw.start_time) : null;
    const end = raw.end_time ? new Date(raw.end_time) : null;
    
    Object.assign(editForm, {
        id: raw.id,
        student_ids: raw.students?.map(s => s.id) || [],
        subject: raw.subject || '',
        topic: raw.topic || '',
        location: raw.location || '',
        description: raw.description || '',
        tanggal: start ? start.toISOString().split('T')[0] : '',
        jam_mulai: start ? start.toTimeString().slice(0, 5) : '',
        jam_selesai: end ? end.toTimeString().slice(0, 5) : '',
    });
    editTimeError.value = '';
    showEditScheduleModal.value = true;
};

const closeEditSchedule = () => showEditScheduleModal.value = false;

const submitEditSchedule = async () => {
    if (!editForm.id) return;
    if (editForm.jam_mulai >= editForm.jam_selesai) {
        editTimeError.value = 'Jam selesai harus lebih besar dari jam mulai.';
        return;
    }
    
    scheduleActionState.submitting = true;
    editTimeError.value = '';
    
    try {
        const payload = {
            student_ids: editForm.student_ids,
            subject: editForm.subject,
            topic: editForm.topic,
            description: editForm.description,
            start_time: combineDateTime(editForm.tanggal, editForm.jam_mulai),
            end_time: combineDateTime(editForm.tanggal, editForm.jam_selesai),
            location: editForm.location || 'Online',
        };
        
        await axios.put(route('guru.api.schedules.update', editForm.id), payload);
        setSuccessMessage('Jadwal berhasil diperbarui!');
        closeEditSchedule();
        fetchSchedules();
    } catch (error) {
        console.error('Error updating schedule:', error);
        editTimeError.value = error.response?.data?.message || 'Gagal memperbarui jadwal.';
    } finally {
        scheduleActionState.submitting = false;
    }
};

const openEditMateri = (schedule) => {
    materiForm.schedule_id = schedule.id;
    materiForm.existing = JSON.parse(JSON.stringify(schedule.materi || []));
    materiForm.materi_uploads = [];
    editMaterialError.value = '';
    showEditMateriModal.value = true;
};
const closeEditMateri = () => showEditMateriModal.value = false;

const submitEditMateri = async () => {
    scheduleActionState.submitting = true;
    try {
        // Update existing materials (handle optional file replacement)
        for (const material of materiForm.existing) {
            // If no new file, keep lightweight JSON update
            if (!material.newFile) {
                await axios.put(route('guru.api.materials.update', material.id), {
                    title: material.judul,
                    description: material.deskripsi,
                });
                continue;
            }

            // When replacing file, use multipart + _method override so backend receives the upload
            const formData = new FormData();
            formData.append('title', material.judul);
            formData.append('description', material.deskripsi || '');
            formData.append('file', material.newFile);
            formData.append('_method', 'PUT');

            await axios.post(route('guru.api.materials.update', material.id), formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
        }

        // Upload new materials
        if (materiForm.materi_uploads.length && materiForm.schedule_id) {
            for (const file of materiForm.materi_uploads) {
                const formData = new FormData();
                formData.append('title', file.name);
                formData.append('description', '');
                formData.append('status', 'Terunggah');
                formData.append('file', file);
                await axios.post(route('guru.api.materials.store', materiForm.schedule_id), formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
            }
        }
        
        setSuccessMessage('Materi berhasil diperbarui!');
        closeEditMateri();
        fetchSchedules();
    } catch (error) {
        console.error('Error updating materials:', error);
        errorMessage.value = 'Gagal memperbarui materi.';
    } finally {
        scheduleActionState.submitting = false;
    }
};

const deleteSchedule = async (schedule) => {
    const result = await Swal.fire({
        title: 'Hapus Jadwal?',
        html: `Apakah Anda yakin ingin menghapus jadwal <strong>${schedule.subject || 'ini'}</strong>?<br><small class="text-slate-500">Data yang dihapus dapat dipulihkan kembali.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-3xl',
            title: 'text-xl font-bold',
            htmlContainer: 'text-sm',
            confirmButton: 'rounded-xl px-6 py-2.5 font-bold shadow-lg',
            cancelButton: 'rounded-xl px-6 py-2.5 font-bold'
        }
    });

    if (!result.isConfirmed) return;

    scheduleActionState.deleting[schedule.id] = true;
    try {
        await axios.delete(route('guru.api.schedules.destroy', schedule.id));
        
        await Swal.fire({
            title: 'Berhasil!',
            text: 'Jadwal berhasil dihapus.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false,
            customClass: {
                popup: 'rounded-3xl',
                title: 'text-xl font-bold'
            }
        });
        
        fetchSchedules();
    } catch (error) {
        console.error('Error deleting schedule:', error);
        
        await Swal.fire({
            title: 'Gagal!',
            text: 'Terjadi kesalahan saat menghapus jadwal.',
            icon: 'error',
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'OK',
            customClass: {
                popup: 'rounded-3xl',
                title: 'text-xl font-bold',
                confirmButton: 'rounded-xl px-6 py-2.5 font-bold'
            }
        });
    } finally {
        scheduleActionState.deleting[schedule.id] = false;
    }
};

const restoreSchedule = async (schedule) => {
    scheduleActionState.restoring[schedule.id] = true;
    try {
        await axios.post(route('guru.api.schedules.restore', schedule.id));
        setSuccessMessage('Jadwal berhasil dipulihkan.');
        fetchSchedules();
    } catch (error) {
        console.error('Error restoring schedule:', error);
        errorMessage.value = 'Gagal memulihkan jadwal.';
    } finally {
        scheduleActionState.restoring[schedule.id] = false;
    }
};

const goToAttendance = (schedule) => {
    const subjectLabel = [schedule?.subject, schedule?.topik || schedule?.topic]
        .filter(Boolean)
        .join('-')
        .replace(/^[-\s]+|[-\s]+$/g, '') || undefined;
    const date = schedule?.waktu_mulai ? String(schedule.waktu_mulai).slice(0, 10) : undefined;
    router.visit(route('guru.attendance', { subject: subjectLabel, date, schedule_id: schedule?.id }));
};

const openMaterialPreview = (f) => {
    if (f.url) window.open(f.url, '_blank');
};

const validateMaterialFiles = (files) => {
    const accepted = [];
    const rejected = [];

    files.forEach((file) => {
        const isAllowedType = ALLOWED_MATERIAL_TYPES.includes(file.type);
        const isAllowedSize = file.size <= MAX_MATERIAL_SIZE;
        if (isAllowedType && isAllowedSize) {
            accepted.push(file);
        } else {
            rejected.push({ name: file.name, reason: !isAllowedType ? 'format' : 'size' });
        }
    });

    return { accepted, rejected };
};

const handleMaterialFiles = (e, form) => {
    const files = Array.from(e.target.files || []);
    const { accepted, rejected } = validateMaterialFiles(files);

    const targetError = form === addForm ? addMaterialError : editMaterialError;

    if (rejected.length) {
        const reasons = rejected.map(r => `${r.name} (${r.reason === 'size' ? 'maks 10MB' : 'hanya PDF/PNG/JPEG'})`).join(', ');
        targetError.value = `file ditolak: ${reasons}`;
    } else {
        targetError.value = '';
    }

    if (accepted.length) {
        form.materi_uploads.push(...accepted);
    }

    // Reset input value to allow uploading the same file again if needed
    e.target.value = '';
};

const removeMaterialFile = (form, file) => {
    const idx = form.materi_uploads.indexOf(file);
    if (idx > -1) form.materi_uploads.splice(idx, 1);
};

const confirmDeleteMaterial = (m) => {
    materialToDelete.value = m;
    showDeleteMaterialModal.value = true;
};

const closeDeleteMaterialModal = () => showDeleteMaterialModal.value = false;

const deleteExistingMaterial = async () => {
    if (!materialToDelete.value) return;
    try {
        await axios.delete(route('guru.api.materials.destroy', materialToDelete.value.id));
        materiForm.existing = materiForm.existing.filter(m => m.id !== materialToDelete.value.id);
        setSuccessMessage('Materi berhasil dihapus.');
        closeDeleteMaterialModal();
        fetchSchedules();
    } catch (error) {
        console.error('Error deleting material:', error);
        errorMessage.value = 'Gagal menghapus materi.';
    }
};

const onReplaceFile = (e, material) => {
    const file = e.target.files[0];
    if (!file) return;

    const { accepted, rejected } = validateMaterialFiles([file]);
    if (rejected.length) {
        editMaterialError.value = `${file.name} ditolak (${rejected[0].reason === 'size' ? 'maks 10MB' : 'hanya PDF/PNG/JPEG'})`;
        e.target.value = '';
        return;
    }

    editMaterialError.value = '';
    material.newFile = accepted[0];
    e.target.value = '';
};

// 1. Hitung tingkatan pendidikan yang tersedia dari data siswa
const availableLevels = computed(() => {
    const levels = props.students
        .map(s => s.education_level)
        .filter(l => l); // Ambil yang tidak null
    return [...new Set(levels)].sort(); // Unique dan urutkan
});

// 2. Cek apakah semua siswa di level tersebut sudah terpilih
const isLevelSelected = (level) => {
    const studentsInLevel = props.students.filter(s => s.education_level === level);
    if (!studentsInLevel.length) return false;
    // Return true jika semua ID siswa di level ini ada di form
    return studentsInLevel.every(s => addForm.student_ids.includes(s.id));
};

// 3. Fungsi untuk memilih/membatalkan semua siswa di level tertentu
const toggleLevel = (level) => {
    const studentsInLevel = props.students.filter(s => s.education_level === level);
    const idsInLevel = studentsInLevel.map(s => s.id);
    const allSelected = isLevelSelected(level);

    if (allSelected) {
        // Jika sudah terpilih semua, batalkan seleksi (hapus ID dari array)
        addForm.student_ids = addForm.student_ids.filter(id => !idsInLevel.includes(id));
    } else {
        // Jika belum, masukkan semua ID ke array (hindari duplikat)
        const currentIds = new Set(addForm.student_ids);
        idsInLevel.forEach(id => currentIds.add(id));
        addForm.student_ids = Array.from(currentIds);
    }
};

</script>
<template>
  <div class="space-y-8">
    <Head title="Jadwal" />

    <!-- Header Section -->
    <section class="flex flex-col gap-6 md:flex-row md:items-end justify-between">
      <div>
         <div class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-orange-50 to-amber-50 px-4 py-1.5 text-xs font-bold text-orange-600 mb-2 border border-orange-200 shadow-md hover:shadow-lg transition-all duration-300 hover:scale-105">
            <span class="relative flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
            </span>
            <ClockIcon class="h-3.5 w-3.5" />
            <span>Manajemen Jadwal</span>
        </div>

        <p class="mt-3 text-lg md:text-xl font-bold text-slate-700 max-w-3xl leading-relaxed tracking-wide transition-all duration-300 hover:text-[#84994f] hover:scale-[1.02] cursor-default">
            Kelola jadwal mengajar dengan efisien dan secara terstruktur.
        </p>
      </div>

      <div class="flex flex-col sm:flex-row gap-3">
           <label class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-600 cursor-pointer hover:bg-slate-50 transition-colors">
            <input
              v-model="filters.withTrashed"
              type="checkbox"
              class="h-4 w-4 rounded border-slate-300 text-[#84994f] focus:ring-[#84994f]"
            />
            <span>Tampilkan Terhapus</span>
          </label>
           <button
            class="group inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-br from-[#84994f] to-[#6b7a3f] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-[#84994f]/30 transition-all hover:scale-105 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:scale-100"
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
        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm group hover:border-[#84994f]/50 transition-colors">
             <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 group-hover:text-[#84994f] transition-colors">Pencarian</label>
             <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <MagnifyingGlassIcon class="h-5 w-5" />
                </div>
                <input
                    v-model="filters.search"
                    type="search"
                    placeholder="Cari topik atau mapel..."
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 pl-11 pr-4 py-3 text-sm font-medium focus:border-[#84994f] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#84994f]/20 transition-all hover:bg-white"
                />
            </div>
        </div>

        <!-- Status Filter -->
        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm group hover:border-[#84994f]/50 transition-colors">
             <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 group-hover:text-[#84994f] transition-colors">Filter Status</label>
             <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <FunnelIcon class="h-5 w-5" />
                </div>
                 <select
                    v-model="filters.status"
                    class="w-full appearance-none rounded-2xl border border-slate-200 bg-slate-50 pl-11 pr-10 py-3 text-sm font-bold text-slate-700 focus:border-[#84994f] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#84994f]/20 transition-all cursor-pointer hover:bg-white"
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
        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm flex items-center justify-between group hover:border-[#84994f]/50 transition-colors hover:shadow-lg">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest group-hover:text-[#84994f] transition-colors">Total Jadwal</label>
                <p class="mt-1 text-3xl font-extrabold text-slate-800">{{ schedulesList?.length || 0 }}</p>
            </div>
            <div class="h-14 w-14 rounded-full bg-[#84994f]/10 border border-[#84994f]/20 flex items-center justify-center text-[#84994f] group-hover:scale-110 transition-transform shadow-sm">
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
                v-for="schedule in schedulesList"
                :key="schedule.id"
                :class="[
                    'group relative rounded-[2.5rem] p-8 shadow-sm transition-all hover:shadow-lg',
                    schedule.is_deleted ? 'border-rose-100 bg-rose-50/30' : 'border border-slate-200 bg-white hover:border-[#84994f]/30'
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
                            <span :class="['rounded-lg px-3 py-1.5 text-xs font-bold border shadow-sm', badgeClass(computeScheduleStatus(schedule))]">
                                {{ computeScheduleStatus(schedule) }}
                            </span>
                             <span v-if="schedule.is_deleted" class="rounded-lg bg-rose-100 px-3 py-1.5 text-xs font-bold text-rose-700 border border-rose-200">
                                Terhapus
                            </span>
                        </div>

                        <div>
                            <h3 class="text-2xl font-extrabold text-slate-800 tracking-tight group-hover:text-[#84994f] transition-colors">{{ schedule.topik }}</h3>
                            <p class="text-sm font-bold text-slate-500 mt-1 flex items-center gap-2">
                                <span class="inline-block h-2 w-2 rounded-full bg-slate-300"></span>
                                {{ schedule.subject }}
                                <span class="text-slate-300 font-normal">•</span>
                                <span class="text-slate-400 font-normal">
                                    {{ schedule.peserta?.length ? `${schedule.peserta.length} Siswa Terdaftar` : 'Belum ada peserta' }}
                                </span>
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-4 text-sm font-medium text-slate-600">
                            <div class="flex items-center gap-2 bg-slate-50 px-3 py-2 rounded-xl border border-slate-100">
                                <ClockIcon class="h-5 w-5 text-[#84994f]" />
                                <span>{{ formatTimeRange(schedule.waktu_mulai, schedule.waktu_selesai) }}</span>
                            </div>
                            <div class="flex items-center gap-2 bg-slate-50 px-3 py-2 rounded-xl border border-slate-100" v-if="schedule.lokasi">
                                <MapPinIcon class="h-5 w-5 text-[#84994f]" />
                                <span>{{ schedule.lokasi }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="w-full lg:w-96 flex flex-col gap-3">
                         <button
                            class="w-full flex items-center justify-center gap-3 rounded-xl bg-[#84994f] px-5 py-3 text-sm font-bold text-white shadow-md shadow-[#84994f]/30 transition-all hover:bg-[#6b7a3f] hover:shadow-lg hover:-translate-y-0.5 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="schedule.is_deleted"
                            @click="goToAttendance(schedule)"
                        >
                            <ClipboardDocumentCheckIcon class="h-5 w-5" />
                            Catat Kehadiran
                        </button>
                        
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
                        Materi Pembelajaran ({{ schedule.materi?.length || 0 }})
                    </h4>
                     <div v-if="!schedule.materi?.length" class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/30 p-6 text-center">
                        <p class="text-sm font-medium text-slate-400">Belum ada materi diunggah.</p>
                    </div>
                     <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <button
                            v-for="file in schedule.materi"
                            :key="file.id"
                            type="button"
                            class="group/file flex items-start gap-3 rounded-2xl border border-slate-200 bg-white p-4 hover:border-[#84994f] hover:ring-2 hover:ring-[#84994f]/20 transition-all text-left shadow-sm"
                            @click="openMaterialPreview(file)"
                        >
                             <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-[#84994f]/20 to-[#84994f]/10 border border-[#84994f]/20 flex items-center justify-center text-[#84994f] shadow-sm group-hover/file:scale-110 transition-transform">
                                <DocumentTextIcon class="h-6 w-6" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-800 truncate group-hover/file:text-[#84994f] transition-colors">{{ file.judul }}</p>
                                <p class="text-xs text-slate-500 mt-0.5 line-clamp-1">{{ file.deskripsi || 'Tanpa deskripsi' }}</p>
                                <div class="mt-2 flex items-center gap-2">
                                     <span :class="['text-[10px] px-2 py-0.5 rounded-full font-bold border', badgeClass(file.status)]">
                                        {{ file.status || 'Terunggah' }}
                                    </span>
                                    <span v-if="file.url" class="text-[10px] font-bold text-[#84994f] underline decoration-[#84994f]/30">Lihat File</span>
                                </div>
                            </div>
                        </button>
                     </div>
                </div>
             </article>

             <div v-if="!schedulesList?.length" class="py-20 flex flex-col items-center justify-center text-center">
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
             <div class="space-y-5">
                <!-- <div class="p-4 rounded-xl bg-[#84994f]/10 border border-[#84994f]/20">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Siswa <span class="text-rose-500">*</span></label>
                    <p v-if="!hasStudents" class="text-xs font-bold text-rose-500 bg-rose-50 p-2 rounded">Belum ada data siswa.</p>
                     <div v-else class="flex flex-wrap gap-2 max-h-40 overflow-y-auto custom-scrollbar">
                        <label
                            v-for="student in props.students"
                            :key="student.id"
                            class="inline-flex cursor-pointer select-none items-center gap-2 rounded-xl border bg-white px-3 py-1.5 text-xs font-bold transition-all hover:bg-slate-50 hover:shadow-xs"
                             :class="addForm.student_ids.includes(student.id) ? 'border-[#84994f] bg-[#84994f]/10 text-[#84994f] ring-1 ring-[#84994f]/30' : 'border-slate-200 text-slate-600'"
                        >
                            <input
                            v-model="addForm.student_ids"
                            :value="student.id"
                            type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-[#84994f] focus:ring-[#84994f]"
                            />
                            <span>{{ student.name }}</span>
                        </label>
                    </div>
                </div> -->
                <div class="p-4 rounded-xl bg-[#84994f]/10 border border-[#84994f]/20">
    <label class="block text-sm font-bold text-slate-700 mb-2">Pilih Siswa <span class="text-rose-500">*</span></label>
    
    <p v-if="!hasStudents" class="text-xs font-bold text-rose-500 bg-rose-50 p-2 rounded">Belum ada data siswa.</p>
    
    <div v-else>
        <div v-if="availableLevels.length" class="mb-3 flex flex-wrap gap-2 pb-3 border-b border-[#84994f]/20">
            <span class="text-xs font-bold text-slate-500 self-center mr-1">Pilih Cepat:</span>
            <button
                v-for="level in availableLevels"
                :key="level"
                type="button"
                @click="toggleLevel(level)"
                class="px-3 py-1.5 rounded-lg text-xs font-bold border transition-all shadow-sm"
                :class="isLevelSelected(level)
                    ? 'bg-[#84994f] text-white border-[#84994f] ring-2 ring-[#84994f]/20'
                    : 'bg-white text-slate-600 border-slate-200 hover:bg-white hover:text-[#84994f] hover:border-[#84994f]'"
            >
                {{ isLevelSelected(level) ? 'Batal ' : 'Pilih Semua ' }} {{ level }}
            </button>
        </div>
        <div class="flex flex-wrap gap-2 max-h-40 overflow-y-auto custom-scrollbar">
            <label
                v-for="student in props.students"
                :key="student.id"
                class="inline-flex cursor-pointer select-none items-center gap-2 rounded-xl border bg-white px-3 py-1.5 text-xs font-bold transition-all hover:bg-slate-50 hover:shadow-xs"
                :class="addForm.student_ids.includes(student.id) ? 'border-[#84994f] bg-[#84994f]/10 text-[#84994f] ring-1 ring-[#84994f]/30' : 'border-slate-200 text-slate-600'"
            >
                <input
                    v-model="addForm.student_ids"
                    :value="student.id"
                    type="checkbox"
                    class="h-4 w-4 rounded border-slate-300 text-[#84994f] focus:ring-[#84994f]"
                />
                <span>
                    {{ student.name }} 
                    <span v-if="student.education_level" class="text-[10px] opacity-60 uppercase">({{ student.education_level }})</span>
                </span>
            </label>
        </div>
    </div>
</div>

                 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Mapel</label>
                        <input v-model="addForm.subject" required type="text" placeholder="e.g. Matematika" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-[#84994f] focus:ring-4 focus:ring-[#84994f]/20 outline-none transition-all" />
                    </div>
                     <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Topik</label>
                        <input v-model="addForm.topic" required type="text" placeholder="e.g. Aljabar Linear" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-[#84994f] focus:ring-4 focus:ring-[#84994f]/20 outline-none transition-all" />
                    </div>
                 </div>

                 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Tanggal</label>
                        <input v-model="addForm.tanggal" required type="date" :min="minScheduleDate" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-[#84994f] focus:ring-4 focus:ring-[#84994f]/20 outline-none transition-all" />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Lokasi</label>
                        <input v-model="addForm.location" type="text" placeholder="e.g. Ruang Kelas A1" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-[#84994f] focus:ring-4 focus:ring-[#84994f]/20 outline-none transition-all" />
                    </div>
                 </div>

                 <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Jam Mulai</label>
                        <input v-model="addForm.jam_mulai" required type="time" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-[#84994f] focus:ring-4 focus:ring-[#84994f]/20 outline-none transition-all" />
                    </div>
                     <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Jam Selesai</label>
                        <input v-model="addForm.jam_selesai" required type="time" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-[#84994f] focus:ring-4 focus:ring-[#84994f]/20 outline-none transition-all" />
                    </div>
                 </div>
                 <p v-if="addTimeError" class="text-xs font-bold text-rose-500">{{ addTimeError }}</p>

                 <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Deskripsi (Opsional)</label>
                    <textarea v-model="addForm.description" rows="2" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-[#84994f] focus:ring-4 focus:ring-[#84994f]/20 outline-none resize-none transition-all"></textarea>
                 </div>

                 <!-- Material Upload -->
                 <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/50">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Upload Materi</label>
                    <label :for="addMaterialInputId" class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer hover:border-[#84994f] hover:bg-white transition-all">
                        <DocumentArrowUpIcon class="h-8 w-8 text-slate-400 mb-2" />
                        <span class="text-sm font-medium text-slate-600">Klik untuk pilih file (PDF/PNG/JPEG dengan ukuran maks 10 MB)</span>
                        <input :id="addMaterialInputId" type="file" class="hidden" multiple @change="handleMaterialFiles($event, addForm)" />
                    </label>
                    <p v-if="addMaterialError" class="mt-2 text-xs font-bold text-rose-500">{{ addMaterialError }}</p>
                    
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
                <button type="submit" :disabled="scheduleActionState.submitting" class="rounded-xl bg-[#84994f] px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-[#84994f]/30 hover:bg-[#6b7a3f] active:scale-95 transition-transform disabled:opacity-50">
                    {{ scheduleActionState.submitting ? 'Menyimpan...' : 'Simpan Jadwal' }}
                </button>
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

                 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Tanggal</label>
                        <input v-model="editForm.tanggal" required type="date" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-sky-500 focus:ring-4 focus:ring-sky-100 outline-none transition-all" />
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Lokasi</label>
                        <input v-model="editForm.location" type="text" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-sky-500 focus:ring-4 focus:ring-sky-100 outline-none transition-all" />
                    </div>
                 </div>

                 <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Jam Mulai</label>
                        <input v-model="editForm.jam_mulai" required type="time" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium focus:border-sky-500 focus:ring-4 focus:ring-sky-100 outline-none transition-all" />
                    </div>
                     <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Jam Selesai</label>
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
                <button type="submit" :disabled="scheduleActionState.submitting" class="rounded-xl bg-sky-500 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-sky-200 hover:bg-sky-600 active:scale-95 transition-transform disabled:opacity-50">
                    {{ scheduleActionState.submitting ? 'Menyimpan...' : 'Update Jadwal' }}
                </button>
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
                         <div class="flex-1 mr-4">
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
                         <input :id="editMaterialInputId" type="file" class="hidden" multiple @change="handleMaterialFiles($event, materiForm)" />
                     </label>
                     <p v-if="editMaterialError" class="mt-2 text-xs font-bold text-rose-500">{{ editMaterialError }}</p>
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
                <button type="submit" :disabled="scheduleActionState.submitting" class="rounded-xl bg-amber-500 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-amber-200 hover:bg-amber-600 active:scale-95 transition-transform disabled:opacity-50">
                    {{ scheduleActionState.submitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
                </button>
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
