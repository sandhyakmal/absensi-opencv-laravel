@extends('admin.layouts.app')

@section('title', 'Data Siswa')
@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>Data Siswa</h4>
                    <a href="{{ route('siswa.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Siswa
                    </a>
                    {{-- <button class="btn btn-primary" id="modal-5" data-title="Tambah Siswa">Tambah Siswa</button> --}}
                </div>
                <!-- Modal -->
                {{-- <form class="modal-part" id="modal-login-part" method="POST" action="{{ route('siswa.store') }}">
                    @csrf

                    <p>This login form is taken from elements with <code>#modal-login-part</code> id.</p>
                    <div class="form-group">
                        <label>Username</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </div>
                            </div>
                            <input type="text" class="form-control" placeholder="Email" name="email">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </div>
                            </div>
                            <input type="password" class="form-control" placeholder="Password" name="password">
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="remember" class="custom-control-input" id="remember-me">
                            <label class="custom-control-label" for="remember-me">Remember Me</label>
                        </div>
                    </div>
                </form> --}}
                <!-- End Modal -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table-1">
                            <thead>
                                <tr>
                                    <th class="text-center">
                                        No.
                                    </th>
                                    <th>NIS</th>
                                    <th>Nama</th>
                                    <th>Kelas</th>
                                    <th>No. Handphone</th>
                                    <th>Alamat</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($siswas as $i => $s)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $s->nis }}</td>
                                        <td>{{ $s->nama }}</td>
                                        <td>{{ $s->kelas ?? '-' }}</td>
                                        <td>{{ $s->no_hp ?? '-' }}</td>
                                        <td>{{ $s->alamat ?? '-' }}</td>
                                        <td>
                                            <a class="btn btn-sm btn-warning"
                                                href="{{ route('siswa.edit', $s->id) }}">Edit</a>

                                            <form action="{{ route('siswa.destroy', $s->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('Yakin hapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger" type="submit">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
