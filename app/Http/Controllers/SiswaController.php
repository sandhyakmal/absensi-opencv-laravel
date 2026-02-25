<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Services\FaceClient;
use Illuminate\Support\Facades\Storage;


class SiswaController extends Controller
{

    public function index()
    {
        $siswas = Siswa::orderBy('nama')->get();
        return view('admin.pages.siswa.index', compact('siswas'));
    }

    public function create()
    {
        return view('admin.pages.siswa.create');
    }

    public function store(Request $request, FaceClient $faceClient)
    {
        // 0) cek FastAPI dulu (python nyala atau tidak)
        if (!$faceClient->ping()) {
            return redirect()->route('siswa.index')
                ->with('error', 'Layanan Face Recognition (Python/FastAPI) sedang tidak aktif. Silakan coba lagi nanti.');
        }

        $data = $request->validate([
            'nis' => ['required','string','max:50','unique:siswas,nis'],
            'nama' => ['required','string','max:255'],
            'kelas' => ['required'],
            'no_hp' => ['nullable','string','max:30'],
            'nama_ortu' => ['nullable','string','max:255'],
            'no_hp_ortu' => ['nullable','string','max:30'],
            'alamat' => ['required','string'],
            'foto' => ['required','image','mimes:jpg,jpeg,png','max:5120'],
        ]);

        // 1) simpan siswa ke DB dulu
        $siswa = Siswa::create([
            'nis' => $data['nis'],
            'nama' => $data['nama'],
            'kelas' => $data['kelas'],
            'no_hp' => $data['no_hp'] ?? null,
            'alamat' => $data['alamat'],
            'nama_ortu' => $data['nama_ortu'] ?? null,
            'no_hp_ortu' => $data['no_hp_ortu'] ?? null,
        ]);

        // 2) simpan foto permanen ke storage/public/siswa/{NIS}/
        $folder = 'siswa/';
        $ext = strtolower($request->file('foto')->getClientOriginalExtension()); // 'jpg' atau 'jpeg'
        $relativePath = $request->file('foto')->storeAs(
            'siswa',
            $siswa->nis . '.' . $ext,
            'public'
        );

        $fullPath = Storage::disk('public')->path($relativePath);

        // optional simpan path di DB
        $siswa->update(['foto_path' => $relativePath]);

        // 3) hit FastAPI /enroll
        // NOTE: enroll id boleh tetap pakai NIS siswa (lebih stabil)
        $res = $faceClient->enroll((string) $siswa->nis, $siswa->nama, $fullPath);

        if (!$res->successful()) {
            // rollback: hapus siswa + hapus file foto permanen
            Storage::disk('public')->delete($relativePath);
            $siswa->delete();

            return back()
                ->withInput()
                ->with('error', 'Enroll wajah gagal: ' . ($res->json('detail') ?? $res->body()));
        }

        return redirect()->route('siswa.index')
            ->with('success', 'Siswa berhasil ditambahkan & wajah berhasil di-enroll.');
    }

    public function edit(Siswa $siswa)
    {
        return view('admin.pages.siswa.edit', compact('siswa'));
    }

    public function update(Request $request, Siswa $siswa, FaceClient $faceClient)
    {
        // 0) cek FastAPI dulu (python nyala atau tidak)
        if (!$faceClient->ping()) {
            return redirect()->route('siswa.index')
                ->with('error', 'Layanan Face Recognition (Python/FastAPI) sedang tidak aktif. Silakan coba lagi nanti.');
        }

        $data = $request->validate([
            // NIS readonly, tapi tetap ikut dikirim untuk display (atau boleh dihapus dari form)
            'nis'           => ['required','string','max:50'],
            'nama'          => ['required','string','max:255'],
            'kelas'         => ['nullable','string','max:50'],
            'no_hp'         => ['nullable','string','max:30'],
            'nama_ortu'     => ['required','string','max:255'],
            'no_hp_ortu'    => ['nullable','string','max:15'],
            'alamat'        => ['nullable','string','max:255'],

            // foto opsional saat edit
            'foto'          => ['nullable','image','mimes:jpg,jpeg','max:5120'],
        ]);

        // pastikan NIS tidak berubah walau dikirim
        unset($data['nis']);

        // update data selain nis
        $siswa->update($data);

        // kalau upload foto baru -> replace + enroll lagi
        if ($request->hasFile('foto')) {
            // hapus foto lama
            if ($siswa->foto_path && Storage::disk('public')->exists($siswa->foto_path)) {
                Storage::disk('public')->delete($siswa->foto_path);
            }

            $ext = strtolower($request->file('foto')->getClientOriginalExtension()); // jpg / jpeg
            $relativePath = $request->file('foto')->storeAs('siswa', $siswa->nis . '.' . $ext, 'public');
            $fullPath = Storage::disk('public')->path($relativePath);

            $siswa->update(['foto_path' => $relativePath]);

            // enroll ulang (id tetap NIS siswa)
            $res = $faceClient->enroll((string) $siswa->nis, $siswa->nama, $fullPath);

            if (!$res->successful()) {
                return back()
                    ->withInput()
                    ->with('error', 'Update berhasil, tapi enroll ulang gagal: ' . ($res->json('detail') ?? $res->body()));
            }
        }

        return redirect()->route('siswa.index')->with('success', 'Siswa berhasil diupdate.');
    }

    public function destroy(Siswa $siswa, FaceClient $faceClient)
    {
        // 0) cek FastAPI dulu (python nyala atau tidak)
        if (!$faceClient->ping()) {
            return redirect()->route('siswa.index')
                ->with('error', 'Layanan Face Recognition (Python/FastAPI) sedang tidak aktif. Silakan coba lagi nanti.');
        }
        // hapus file foto dari storage (yang sudah kamu punya)
        if (!empty($siswa->foto_path) && Storage::disk('public')->exists($siswa->foto_path)) {
            Storage::disk('public')->delete($siswa->foto_path);
        }

        // hapus face data di FastAPI (id harus sesuai saat enroll)
        $faceClient->delete($siswa->nis); // <-- kalau enroll pakai NIS
        // $faceClient->delete((string)$siswa->id); // <-- kalau enroll pakai id siswa

        $siswa->delete();

        return redirect()->route('siswa.index')->with('success', 'Siswa berhasil dihapus.');
    }
}
