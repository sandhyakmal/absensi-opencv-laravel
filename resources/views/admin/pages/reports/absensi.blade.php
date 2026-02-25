@extends('admin.layouts.app')

@section('title', 'Report Absensi')
@section('content')

    <div class="row">
        <div class="col-12 col-md-12 col-lg-12">

            <div class="card">
                <div class="card-header">
                    <h4>Laporan Absensi Siswa</h4>
                </div>
                <div class="card-body">

                    <form method="GET" action="{{ route('reports.absensi.export') }}">
                        {{-- @csrf --}}
                        <div class="form-group">
                            <label>Tanggal Absensi</label>
                            <input name="tanggal" type="text" class="form-control datepicker">
                        </div>

                        <div class="card-footer text-right">
                            <button class="btn btn-primary">Export</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection
