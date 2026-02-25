@extends('admin.layouts.app')

@section('title', 'Tambah Siswa')
@section('content')
    <div class="row">
        <div class="col-12 col-md-12 col-lg-12">
            <div class="card">
                <form method="POST" action="{{ route('siswa.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-header">
                        <h4>Tambah Data Siswa</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>NIS</label>
                            <input name="nis" type="text" class="form-control" required="">
                        </div>

                        <div class="form-group">
                            <label>Nama</label>
                            <input name="nama" type="text" class="form-control" required="">
                        </div>

                        <div class="form-group">
                            <label>Kelas</label>
                            <select class="form-control select2" name="kelas">
                                <option value="1">Kelas 1</option>
                                <option value="2">Kelas 2</option>
                                <option value="3">Kelas 3</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>No. Handphone</label>
                            <input name="no_hp" type="text" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Nama Orang Tua</label>
                            <input name="nama_ortu" type="text" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>No. Handphone Orang Tua</label>
                            <input name="no_hp_ortu" type="text" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Chat ID Notifikasi</label>
                            <input name="chat_id" type="text" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Alamat</label>
                            <textarea name="alamat" class="form-control" required=""></textarea>
                        </div>

                        <div class="form-group">
                            <label>Foto Wajah (JPG/PNG)</label>
                            <input type="file" name="foto" id="foto" class="form-control"
                                accept="image/png,image/jpeg" required>
                            <small class="text-muted">Gunakan foto wajah jelas, 1 orang saja.</small>

                            <div class="mt-3">
                                <img id="fotoPreview" src="" alt="Preview"
                                    style="display:none; max-width:220px; border-radius:10px; border:1px solid #ddd;">
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
        document.getElementById('foto').addEventListener('change', function(e) {
            const file = e.target.files?.[0];
            const img = document.getElementById('fotoPreview');
            if (!file) {
                img.style.display = 'none';
                img.src = '';
                return;
            }

            img.src = URL.createObjectURL(file);
            img.style.display = 'block';
        });
    </script>
@endpush
