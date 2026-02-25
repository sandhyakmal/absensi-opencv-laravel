<aside id="sidebar-wrapper">
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}">ABSENSI OPENCV</a>
    </div>
    <div class="sidebar-brand sidebar-brand-sm">
        <a href="{{ route('dashboard') }}">OCV</a>
    </div>
    <ul class="sidebar-menu">
        <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="fas fa-fire"></i> <span>Dashboard</span>
            </a>
        </li>
        <li class="{{ request()->routeIs('absensi.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('absensi.index') }}">
                <i class="far fa-file"></i><span> Absensi</span>
            </a>
        </li>
        </li>
        <li class="{{ request()->routeIs('siswa.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('siswa.index') }}">
                <i class="fas fa-user"></i> <span>Data Siswa</span>
            </a>
        </li>
        <li class="dropdown {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <a href="#" class="nav-link has-dropdown"><i class="far fa-file-alt"></i> <span>Laporan</span></a>
            <ul class="dropdown-menu">
                <li class="{{ request()->routeIs('reports.siswa') ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('reports.siswa') }}">Laporan Data Siswa</a></li>
                <li class="{{ request()->routeIs('reports.absensi') ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('reports.absensi') }}">Laporan Absensi</a></li>
            </ul>
        </li>
        {{-- <li class="dropdown {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <a href="#" class="nav-link has-dropdown"><i class="fas fa-ellipsis-h"></i> <span>Setting</span></a>
            <ul class="dropdown-menu">
                <li class="{{ request()->routeIs('reports.siswa') ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('reports.siswa') }}">Laporan Data Siswa</a></li>
                <li class="{{ request()->routeIs('reports.absensi') ? 'active' : '' }}"><a class="nav-link"
                        href="{{ route('reports.absensi') }}">Laporan Absensi</a></li>
            </ul>
        </li> --}}
    </ul>
</aside>
