@php
    $isReportOpen = Request::is('reporte-*');
    $isConfigOpen = Request::is('users*')
        || Request::is('cargos*')
        || Request::is('departamentos*')
        || Request::is('proveedores*')
        || Request::is('ciudades*')
        || Request::is('sucursales*')
        || Request::is('marcas*')
        || Request::is('permissions*')
        || Request::is('cajas*')
        || Request::is('roles*');
@endphp

{{-- Productos --}}
<li class="nav-item">
    <a href="{{ route('productos.index') }}" class="nav-link {{ Request::is('productos*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-box"></i>
        <p>Productos</p>
    </a>
</li>

{{-- Stock --}}
<li class="nav-item">
    <a href="{{ route('stock.index') }}" class="nav-link {{ Request::is('stock*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-warehouse"></i>
        <p>Stock</p>
    </a>
</li>

{{-- Ventas --}}
<li class="nav-item">
    <a href="{{ route('ventas.index') }}" class="nav-link {{ Request::is('ventas*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-shopping-basket"></i>
        <p>Ventas</p>
    </a>
</li>
{{-- Cuentas A Cobrar --}}
<li class="nav-item">
    <a href="{{ route('cuentasacobrar.index') }}" class="nav-link {{ Request::is('cuentasacobrar*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-money-bill-wave"></i>
        <p>Cuentas A Cobrar</p>
    </a>
</li>

{{-- Pedidos --}}
<li class="nav-item">
    <a href="{{ route('pedidos.index') }}" class="nav-link {{ Request::is('pedidos*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-shopping-cart"></i>
        <p>Pedidos</p>
    </a>
</li>

{{-- Compras --}}
<li class="nav-item">
    <a href="{{ route('compras.index') }}" class="nav-link {{ Request::is('compras*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-shopping-bag"></i>
        <p>Compras</p>
    </a>
</li>

{{-- Clientes --}}
<li class="nav-item">
    <a href="{{ route('clientes.index') }}" class="nav-link {{ Request::is('clientes*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-users"></i>
        <p>Clientes</p>
    </a>
</li>

{{-- Reportes --}}
<li class="nav-item {{ $isReportOpen ? 'menu-is-opening menu-open' : '' }}">
    <a href="#" class="nav-link">
        <i class="nav-icon fas fa-chart-bar"></i>
        <p>
            Reportes
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ url('reporte-cargos') }}" class="nav-link {{ Request::is('reporte-cargos*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-address-card"></i>
                <p>Reporte cargos</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ url('auditoria') }}" class="nav-link {{ Request::is('auditoria*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-lock"></i>
                <p>Auditoria</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ url('reporte-clientes') }}" class="nav-link {{ Request::is('reporte-clientes*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-users"></i>
                <p>Reporte clientes</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ url('reporte-proveedores') }}" class="nav-link {{ Request::is('reporte-proveedores*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-archive"></i>
                <p>Reporte proveedores</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ url('reporte-productos') }}" class="nav-link {{ Request::is('reporte-productos*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-box"></i>
                <p>Reporte productos</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ url('reporte-sucursales') }}" class="nav-link {{ Request::is('reporte-sucursales*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-building"></i>
                <p>Reporte sucursales</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ url('reporte-ventas') }}" class="nav-link {{ Request::is('reporte-ventas*') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart"></i>
                <p>Reporte ventas</p>
            </a>
        </li>
    </ul>
</li>

{{-- Configuraciones --}}
<li class="nav-item {{ $isConfigOpen ? 'menu-is-opening menu-open' : '' }}">
    <a href="#" class="nav-link">
        <i class="nav-icon fas fa-cogs"></i>
        <p>
            Configuraciones
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('departamentos.index') }}" class="nav-link {{ Request::is('departamentos*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-align-justify"></i>
                <p>Departamentos</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('ciudades.index') }}" class="nav-link {{ Request::is('ciudades*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-address-book"></i>
                <p>Ciudades</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('sucursales.index') }}" class="nav-link {{ Request::is('sucursales*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-building"></i>
                <p>Sucursales</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('cajas.index') }}" class="nav-link {{ Request::is('cajas*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-cash-register"></i>
                <p>Cajas</p>
            </a>
        </li>

        @can('cargos index')
        <li class="nav-item">
            <a href="{{ route('cargos.index') }}" class="nav-link {{ Request::is('cargos*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-address-card"></i>
                <p>Cargos</p>
            </a>
        </li>
        @endcan

        <li class="nav-item">
            <a href="{{ route('marcas.index') }}" class="nav-link {{ Request::is('marcas*') ? 'active' : '' }}">
                <i class="nav-icon fa fa-tag"></i>
                <p>Marcas</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('proveedores.index') }}" class="nav-link {{ Request::is('proveedores*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-archive"></i>
                <p>Proveedores</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('users.index') }}" class="nav-link {{ Request::is('users*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-users"></i>
                <p>Usuarios</p>
            </a>
        </li>

        @can('permissions index')
        <li class="nav-item">
            <a href="{{ route('permissions.index') }}" class="nav-link {{ Request::is('permissions*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-user-shield"></i>
                <p>Permisos</p>
            </a>
        </li>
        @endcan

        @can('roles index')
        <li class="nav-item">
            <a href="{{ route('roles.index') }}" class="nav-link {{ Request::is('roles*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-user-tag"></i>
                <p>Roles</p>
            </a>
        </li>
        @endcan
    </ul>
</li>
