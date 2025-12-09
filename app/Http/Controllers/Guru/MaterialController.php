<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guru\StoreMaterialRequest;
use App\Http\Requests\Guru\UpdateMaterialRequest;
use App\Models\Material;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    protected string $materialDisk = 'public';

    public function store(StoreMaterialRequest $request, Schedule $schedule)
    {
        $this->authorizeSchedule($schedule, $request->user()->id);

        $attributes = $this->buildPayload($request);
        $attributes['schedule_id'] = $schedule->id;
        $attributes['uploaded_by'] = $request->user()->id;

        $material = Material::create($attributes);

        return response()->json($material->fresh('schedule'), 201);
    }

    public function update(UpdateMaterialRequest $request, Material $material)
    {
        $this->authorizeSchedule($material->schedule, $request->user()->id);

        $material->update($this->buildPayload($request, $material));

        return response()->json($material->fresh('schedule'));
    }

    public function destroy(Request $request, Material $material)
    {
        $this->authorizeSchedule($material->schedule, $request->user()->id);

        if ($material->file_path && Storage::disk($this->materialDisk)->exists($material->file_path)) {
            Storage::disk($this->materialDisk)->delete($material->file_path);
        }

        $material->delete();

        return response()->noContent();
    }

    protected function buildPayload(Request $request, ?Material $material = null): array
    {
        $payload = $request->validated();
        $file = $request->file('file');

        if ($file) {
            $disk = Storage::disk($this->materialDisk);

            if ($material && $material->file_path && $disk->exists($material->file_path)) {
                $disk->delete($material->file_path);
            }

            $path = $file->store('materials', $this->materialDisk);
            $payload['file_path'] = $path;
            $payload['file_type'] = $file->getClientOriginalExtension();
            $payload['file_size'] = $file->getSize();
            $payload['download_url'] = $disk->url($path);
        }

        return $payload;
    }

    protected function authorizeSchedule(?Schedule $schedule, int $teacherId): void
    {
        abort_if(! $schedule, 404, 'Jadwal tidak ditemukan');
        abort_unless($schedule->teacher_id === $teacherId, 403, 'Anda tidak boleh mengubah materi ini.');
    }
}
