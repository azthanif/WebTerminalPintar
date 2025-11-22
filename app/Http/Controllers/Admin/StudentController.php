<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Student\StoreStudentRequest;
use App\Http\Requests\Admin\Student\UpdateStudentRequest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $studentsQuery = Student::query()
            ->with('parent:id,name')
            ->when($search, function ($q) use ($search) {
                $q->where('student_id', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('education_level', 'like', "%{$search}%");
            })
            ->orderBy('name');

        $students = $studentsQuery
            ->paginate(8)
            ->withQueryString();

        // Statistik
        $stats = [
            'total'    => Student::count(),
            'aktif'    => Student::where('status', 'active')->count(),
            'nonaktif' => Student::where('status', 'inactive')->count(),
            'most_education' => Student::select('education_level')
                ->whereNotNull('education_level')
                ->groupBy('education_level')
                ->orderByRaw('COUNT(*) DESC')
                ->limit(1)
                ->value('education_level') ?? 'N/A',
        ];

        // Shape data untuk frontend (sesuai template kamu)
        $students->getCollection()->transform(function (Student $student) {
            return [
                'id'              => $student->id,
                'student_id'      => $student->student_id,
                'name'            => $student->name,
                'education_level' => $student->education_level,
                'parent_name'     => $student->parent->name ?? '-',
                'status'          => $student->status === 'active' ? 'Aktif' : 'Nonaktif',
            ];
        });

        return Inertia::render('Admin/Students/Index', [
            'students' => $students,
            'stats'    => $stats,
            'filters'  => [
                'search' => $search,
            ],
        ]);
    }

    public function create()
    {
        // dropdown orang tua (role = ortu)
        $parents = User::whereHas('role', fn ($q) => $q->where('name', 'ortu'))
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $educationOptions = [
            'Belum Sekolah',
            'SD',
            'SMP',
            'SMA/SMK',
            'Kuliah',
            'Lainnya',
        ];

        return Inertia::render('Admin/Students/Form', [
            'student'          => null,
            'parents'          => $parents,
            'educationOptions' => $educationOptions,
        ]);
    }

    public function store(StoreStudentRequest $request)
    {
        Student::create($request->validated());

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function edit(Student $student)
    {
        $parents = User::whereHas('role', fn ($q) => $q->where('name', 'ortu'))
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $educationOptions = [
            'Belum Sekolah',
            'SD',
            'SMP',
            'SMA/SMK',
            'Kuliah',
            'Lainnya',
        ];

        return Inertia::render('Admin/Students/Form', [
            'student' => [
                'id'              => $student->id,
                'student_id'      => $student->student_id,
                'name'            => $student->name,
                'education_level' => $student->education_level,
                'parent_id'       => $student->parent_id,
                'status'          => $student->status,
                'date_of_birth'   => optional($student->date_of_birth)->format('Y-m-d'),
                'school_name'     => $student->school_name,
                'address'         => $student->address,
            ],
            'parents'          => $parents,
            'educationOptions' => $educationOptions,
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $student->update($request->validated());

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Data siswa berhasil dihapus.');
    }
}
