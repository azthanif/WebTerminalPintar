<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'       => $this->id,

            // nama/telepon versi Indonesia
            'nama'     => $this->name,
            'telepon'  => $this->phone,

            // tetap kirim versi "name/phone" kalau dipakai di tempat lain
            'name'     => $this->name,
            'phone'    => $this->phone,

            'email'    => $this->email,

            'role'     => $this->role ? [
                'id'        => $this->role->id,
                'name'      => $this->role->name,
                'display_name' => $this->role->display_name ?? ucfirst($this->role->name),
                'nama_role' => $this->role->display_name ?? ucfirst($this->role->name),
            ] : null,

            'is_active'     => (bool) $this->is_active,

            // tanggal mentah untuk diformat di Vue
            'created_at'    => optional($this->created_at)->toDateString(),
            'last_login_at' => optional($this->last_login_at)->toDateTimeString(),

            // kalau modul lama masih butuh
            'status_label'  => $this->is_active ? 'Aktif' : 'Nonaktif',
            'joined_at'     => optional($this->created_at)->format('d/m/y'),
        ];
    }
}
