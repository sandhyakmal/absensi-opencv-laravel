@extends('admin.layouts.app')

@section('title', 'Edit Siswa')
@section('content')

    <div class="row">
        <div class="col-12 col-md-12 col-lg-12">
            <div class="card">
                <form method="POST" action="{{ route('siswa.update', $siswa->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-header">
                        <h4>Edit Data Siswa</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>NIS</label>
                            <input name="nis" value="{{ old('nis', $siswa->nis) }}" readonly class="form-control"
                                required>
                        </div>

                        <div class="form-group">
                            <label>Nama</label>
                            <input name="nama" value="{{ old('nama', $siswa->nama) }}" class="form-control" required>
                        </div>


                        <div class="form-group">
                            <label>Kelas</label>
                            <select class="form-control select2" name="kelas">
                                <option value="1" {{ old('kelas', $siswa->kelas) == 1 ? 'selected' : '' }}>Kelas 1
                                </option>
                                <option value="2" {{ old('kelas', $siswa->kelas) == 2 ? 'selected' : '' }}>Kelas 2
                                </option>
                                <option value="3" {{ old('kelas', $siswa->kelas) == 3 ? 'selected' : '' }}>Kelas 3
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>No Handphone</label>
                            <input name="no_hp" value="{{ old('no_hp', $siswa->no_hp) }}" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Nama Orang Tua</label>
                            <input name="nama_ortu" value="{{ old('nama_ortu', $siswa->nama_ortu) }}" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>No Handphone Orang Tua</label>
                            <input name="no_hp_ortu" value="{{ old('no_hp_ortu', $siswa->no_hp_ortu) }}"
                                class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Chat ID Notifikasi</label>
                            <input name="chat_id" value="{{ old('chat_id', $siswa->chat_id) }}" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Alamat</label>
                            <input name="alamat" value="{{ old('alamat', $siswa->alamat) }}" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Foto Wajah (JPG/JPEG)</label>

                            @if (!empty($siswa->foto_path))
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $siswa->foto_path) }}" alt="Foto Lama"
                                        style="max-width:220px;border-radius:10px;border:1px solid #ddd;">
                                    <div class="text-muted mt-1">Foto saat ini</div>
                                </div>
                            @else
                                <div class="text-muted mb-2">Belum ada foto.</div>
                            @endif

                            <input type="file" name="foto" id="foto" class="form-control" accept="image/jpeg">
                            <small class="text-muted">Kosongkan jika tidak ingin mengganti foto.</small>

                            <div class="mt-3">
                                <img id="fotoPreview" src=""
                                    style="display:none;max-width:220px;border-radius:10px;border:1px solid #ddd;">
                                <div id="previewText" class="text-muted mt-1" style="display:none;">Preview foto baru</div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer text-right">
                        <button class="btn btn-primary">Simpan</button>
                        <a href="{{ route('siswa.index') }}" class="btn btn-light">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.getElementById('foto')?.addEventListener('change', function(e) {
            const file = e.target.files?.[0];
            const img = document.getElementById('fotoPreview');
            const txt = document.getElementById('previewText');
            if (!file) {
                img.style.display = 'none';
                img.src = '';
                txt.style.display = 'none';
                return;
            }
            img.src = URL.createObjectURL(file);
            img.style.display = 'block';
            txt.style.display = 'block';
        });
    </script>
@endpush
