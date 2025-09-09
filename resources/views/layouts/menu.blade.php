<!-- need to remove -->
<li class="nav-item">
    <a href="{{ route('home') }}" class="nav-link {{ Request::is('home') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Home</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('marcas.index') }}" 
        class="nav-link {{ Request::is('marcas*') ? 'active' : '' }}">
        <i class="fas fa-tags"></i>
        <p>Marcas</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('productos.index') }}" 
        class="nav-link {{ Request::is('productos*') ? 'active' : '' }}">
        <i class="fas fa-box"></i>
        <p>Productos</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('sucursales.index') }}" class="nav-link {{ Request::is('sucursales*') ? 'active' : '' }}">
        <i class="fa fa-building"></i>
        <p>Sucursales</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('cajas.index') }}" class="nav-link {{ Request::is('cajas*') ? 'active' : '' }}">
        <i class="fa fa-cash-register"></i>
        <p>Cajas</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('ventas.index') }}" class="nav-link {{ Request::is('ventas*') ? 'active' : '' }}">
        <i class="fas fa-shopping-basket"></i>
        <p>Ventas</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('pedidos.index') }}" class="nav-link {{ Request::is('pedidos*') ? 'active' : '' }}">
        <i class="fas fa-shopping-cart"></i>
        <p>Pedidos</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('clientes.index') }}" class="nav-link {{ Request::is('clientes*') ? 'active' : '' }}">
        <i class="fas fa-users"></i>
        <p>Clientes</p>
    </a>
</li>

<!-- Clientes -->
<li class="nav-item {{ 
    Request::is('reporte-cargos*') || 
    Request::is('reporte-clientes*') ? 'menu-is-opening menu-open' : '' }}
">
    <a href="#" class="nav-link">
        <i class="fas fa-cogs"></i>
        <p>
            Reportes
            <i class="fas fa-angle-left right"></i>
        </p>
    </a>
    <ul class="nav nav-treeview" style="display: {{ 
        Request::is('reporte-cargos*') ||
        Request::is('reporte-clientes*')
        ? 'block;' : 'none;' }};">
        <li class="nav-item">
            <a href="{{ url('reporte-cargos') }}" class="nav-link {{ Request::is('reporte-cargos*') ? 'active' : '' }}">
                <i class="fas fa-address-card"></i>
                <p>Reporte cargos</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ url('reporte-clientes') }}" class="nav-link {{ Request::is('reporte-clientes*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <p>Reporte clientes</p>
            </a>
        </li>
    </ul>
</li>

<!-- Mostrar colapsado si esta en una de estas opciones del menú tanto en la etiqueta <li> y <ul> -->
<li class="nav-item {{ 
    Request::is('users*') || 
    Request::is('cargos*') || 
    Request::is('departamentos*') || 
    Request::is('proveedores*') || 
    Request::is('ciudades*') ? 'menu-is-opening menu-open' : '' }}
">
    <a href="#" class="nav-link">
        <i class="fas fa-cogs"></i>
        <p>
            Configuraciones
            <i class="fas fa-angle-left right"></i>
        </p>
    </a>
    <ul class="nav nav-treeview" style="display: {{ 
        Request::is('users*') || 
        Request::is('cargos*') || 
        Request::is('departamentos*') || 
        Request::is('proveedores*') || 
        Request::is('ciudades*')
        ? 'block;' : 'none;' }};">
        <li class="nav-item">
            <a href="{{ route('cargos.index') }}" class="nav-link {{ Request::is('cargos*') ? 'active' : '' }}">
                <i class="fas fa-address-card"></i>
                <p>Cargos</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('departamentos.index') }}"
                class="nav-link {{ Request::is('departamentos*') ? 'active' : '' }}">
                <i class="fas fa-align-justify"></i>
                <p>Departamentos</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('proveedores.index') }}"
                class="nav-link {{ Request::is('proveedores*') ? 'active' : '' }}">
                <i class="fas fa-archive"></i>
                <p>Proveedores</p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('ciudades.index') }}" class="nav-link {{ Request::is('ciudades*') ? 'active' : '' }}">
                <i class="fas fa-address-book"></i>
                <p>Ciudades</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('users.index') }}" class="nav-link {{ Request::is('users*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <p>Usuarios</p>
            </a>
        </li>
    </ul>
</li>