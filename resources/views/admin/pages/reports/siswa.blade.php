@extends('admin.layouts.app')

@section('title', 'Report Siswa')
@section('content')

    <div class="row">
        <div class="col-12 col-md-12 col-lg-12">

            <div class="card">
                <div class="card-header">
                    <h4>Laporan Data Siswa</h4>
                </div>
                <div class="card-body">

                    <form method="GET" action="{{ route('reports.siswa.export') }}">
                        {{-- @csrf --}}

                        <div class="form-group">
                            <label>Kelas</label>
                            <select class="form-control select2" name="kelas">
                                <option value="X">Kelas X</option>
                                <option value="XI">Kelas XI</option>
                                <option value="XII">Kelas XII</option>
                            </select>
                        </div>

                        <div class="card-footer text-right">
                            <button class="btn btn-primary">Export</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    @endsection
