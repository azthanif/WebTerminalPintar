<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        $role = $this->roles->first();
        
        $roleData = $role ? [
            'id'           => $role->id,
            'name'         => $role->name,
            'display_name' => $role->display_name ?? ucfirst($role->name),
            'nama_role'    => $role->display_name ?? ucfirst($role->name),
        ] : null;

        return [
            'id'       => $this->id,

            // nama/telepon versi Indonesia
            'nama'     => $this->name,
            'telepon'  => $this->phone,

            // tetap kirim versi "name/phone" kalau dipakai di tempat lain
            'name'     => $this->name,
            'phone'    => $this->phone,

            'email'    => $this->email,

            'role'     => $roleData,

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
