<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\StoreTeacherNoteRequest;
use App\Http\Requests\Guru\UpdateTeacherNoteRequest;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\TeacherNote;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Guru/Catatan', [
            'students' => Student::orderBy('name')->get(['id', 'name']),
            'schedules' => Schedule::where('teacher_id', $request->user()->id)
                ->orderByDesc('start_time')
                ->get(['id', 'topic']),
        ]);
    }

    public function list(Request $request)
    {
        $notes = TeacherNote::with(['student', 'schedule'])
            ->where('teacher_id', $request->user()->id)
            ->latest('recorded_at')
            ->paginate(10);

        return response()->json($notes);
    }

    public function store(StoreTeacherNoteRequest $request)
    {
        $payload = $request->validated();
        $payload['teacher_id'] = $request->user()->id;
        $payload['recorded_at'] = $payload['recorded_at'] ?? now();

        $note = TeacherNote::create($payload)->load(['student', 'schedule']);

        return response()->json($note, 201);
    }

    public function update(UpdateTeacherNoteRequest $request, TeacherNote $teacherNote)
    {
        $this->authorizeNote($teacherNote, $request->user()->id);

        $teacherNote->update($request->validated());

        return response()->json($teacherNote->fresh(['student', 'schedule']));
    }

    public function destroy(Request $request, TeacherNote $teacherNote)
    {
        $this->authorizeNote($teacherNote, $request->user()->id);

        $teacherNote->delete();

        return response()->noContent();
    }

    protected function authorizeNote(TeacherNote $note, int $teacherId): void
    {
        abort_unless($note->teacher_id === $teacherId, 403, 'Catatan ini bukan milik Anda.');
    }
}
