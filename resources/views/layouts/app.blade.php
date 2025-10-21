<x-laravel-ui-adminlte::adminlte-layout>
    <head>
        <title>LP@2</title>
        <link rel="icon" type="image/x-icon" href="">
        <!-- librerias css select2 -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
        <!-- personalizar estilos de select 2  -->
        <style>
            .select2-container .select2-selection--single {
                box-sizing: border-box;
                cursor: pointer;
                display: block;
                height: 38px;
                user-select: none;
                -webkit-user-select: none;
            }

            .select2-container--default .select2-selection--multiple .select2-selection__rendered {
                box-sizing: border-box;
                list-style: none;
                margin: 0;
                padding: 4px 5px;
                width: 100%;
            }

            .select2-container--default .select2-selection--multiple .select2-selection__choice {
                background-color: #3c8dbc;
                border-color: #367fa9;
                padding: 1px 10px;
                color: #fff;
            }

            .select2-container--default .select2-selection--single {
                background-color: #fff;
                border-radius: 3px;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 35px;
                position: absolute;
                top: 1px;
                right: 1px;
                width: 20px;
            }
        </style>
    </head>
    
    <body class="hold-transition sidebar-mini layout-fixed text-sm">
        <div class="wrapper">

            <!-- NAVBAR -->
            <nav class="main-header navbar navbar-expand navbar-white navbar-light" role="navigation" aria-label="Barra de navegación principal">
                <!-- Left -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Alternar menú lateral">
                            <i class="fas fa-bars"></i>
                        </a>
                    </li>
                    @hasSection('content_header')
                        <li class="nav-item d-none d-md-inline-block">
                            <span class="navbar-text ml-2">@yield('content_header')</span>
                        </li>
                    @endif
                </ul>

                <!-- SEARCH -->
                <form class="form-inline ml-3 d-none d-md-flex" method="GET" action="{{ route('search') }}">
                    <div class="input-group input-group-sm">
                        <input class="form-control form-control-navbar" type="search" name="q"
                               placeholder="Buscar…" aria-label="Buscar">
                        <div class="input-group-append">
                            <button class="btn btn-navbar" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Right -->
                <ul class="navbar-nav ml-auto align-items-center">

                    <!-- Fullscreen -->
                    <li class="nav-item d-none d-sm-inline-block">
                        <a class="nav-link" data-widget="fullscreen" href="#" role="button" aria-label="Pantalla completa">
                            <i class="fas fa-expand-arrows-alt"></i>
                        </a>
                    </li>

                    <!-- Dark mode toggle -->
                    <li class="nav-item d-none d-sm-inline-block">
                        <a class="nav-link" href="#" id="darkModeToggle" aria-label="Alternar modo oscuro">
                            <i class="far fa-moon"></i>
                        </a>
                    </li>

                    <!-- NOTIFICACIONES (removido) -->
                    {{-- <li class="nav-item dropdown"> ... </li> --}}

                    <!-- User -->
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Menú de usuario">
                            <img src="{{ Auth::user()->avatar_url ?? 'https://assets.infyom.com/logo/blue_logo_150x150.png' }}"
                                 class="user-image img-circle elevation-2" alt="User Image">
                            <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <li class="user-header bg-primary">
                                <img src="{{ Auth::user()->avatar_url ?? 'https://assets.infyom.com/logo/blue_logo_150x150.png' }}"
                                     class="img-circle elevation-2" alt="User Image">
                                <p>
                                    {{ Auth::user()->name }}
                                    <small>Miembro desde {{ Auth::user()->created_at?->format('M. Y') }}</small>
                                </p>
                            </li>
                            <li class="user-footer">
                                <a href="{{ url('perfil') }}" class="btn btn-default btn-flat">Perfil</a>
                                <a href="#" class="btn btn-default btn-flat float-right"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Salir
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>

            <!-- SIDEBAR -->
            @include('layouts.sidebar')

            <!-- CONFIRM DELETE MODAL -->
            @include('components.confirm-delete-modal')
            <script defer src="{{ asset('js/modal-confirm.js') }}"></script>
            <!-- CONFIRM MODAL -->
            @include('components.confirm-modal')

            <!-- CONTENT WRAPPER -->
            <div class="content-wrapper">
                @yield('content')
            </div>

            <!-- FOOTER -->
            <footer class="main-footer">
                <div class="float-right d-none d-sm-inline">
                    <b>Versión</b> {{ config('app.version', '3.1.0') }}
                </div>
                <strong>&copy; {{ now()->year }} {{ config('app.name', 'Aplicación') }}.</strong> Todos los derechos reservados.
            </footer>
        </div>

        <!-- Dark mode toggle -->
        <script>
            (function () {
                const key = 'adminlte-theme';
                const body = document.body;
                const toggle = document.getElementById('darkModeToggle');

                const saved = localStorage.getItem(key);
                if (saved === 'dark') body.classList.add('dark-mode');

                if (toggle) {
                    toggle.addEventListener('click', function (e) {
                        e.preventDefault();
                        body.classList.toggle('dark-mode');
                        localStorage.setItem(key, body.classList.contains('dark-mode') ? 'dark' : 'light');
                    });
                }
            })();
        </script>
        <!-- REQUIRED SCRIPTS -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- librerias js select2 -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
        
        <!-- cargar codigo javascript desde los blade -->
        @stack('scripts')
        
        <!-- CUSTOM SCRIPTS -->
        <script>
            $(document).ready(function() {
                // Inicializar select2 en los elementos con la clase .select2
                $('.select2').select2({
                    placeholder: "Selecciona una opción", // Placeholder
                    allowClear: true, // Permite limpiar la selección
                    width: '100%' // Ancho del select
                });

                //sweetalert para confirmacion de borrado
                $('.alert-delete').click(function(event) {
                    var form = $(this).closest("form");
                    event.preventDefault();
                    let valor = $(this).data("mensaje") ||
                    "este registro"; // Valor por defecto si no hay data-mensaje
                    Swal.fire({
                            title: "Atención",
                            text: `Desea borrar ${valor}?`, // valor recibido de data-mensaje del boton borrar
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: "Confirmar",
                            cancelButtonText: "Cancelar",
                        })
                        .then(resultado => {
                            if (resultado.value) {
                                form.submit();
                            }
                        });
                });

                /** bucador mediante peticiones fetch*/
                $('.buscar').on('keyup', function() {
                    var query = this.value; // valor del input buscar
                    // Obtener el data-url del parametro input
                    var url = this.getAttribute('data-url');
                    // Fetch para realizar peticion de busqueda
                    fetch(url + '?buscar=' +
                            encodeURIComponent(query), {
                                method: 'GET',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest' // Este encabezado indica una solicitud AJAX
                                }
                            })
                        // respuesta del servidor
                        .then(response => {
                            if (!response.ok) {
                                alert('Error en la consulta');
                                throw new Error('Error en la respuesta del servidor');
                            }
                            // se espera un HTML como respuesta
                            return response.text();
                        })
                        .then(data => {
                            // cargar devuelta el html tabla según lo filtrado
                            $('.tabla-container').html(data);
                        })
                        .catch(error => { // manejar si hay errores en la consulta
                            console.error('Hubo un problema con la solicitud Fetch:', error);
                        });
                });
            });

            //formato de numeros separador de miles
            function format(input) {
                // Eliminar puntos previos para evitar problemas con el replace
                var num = input.value.replace(/\./g, '');

                // Verificar si el valor es un número válido
                if (!isNaN(num)) {
                    // Invertir el string y aplicar la lógica del separador de miles
                    num = num.split('').reverse().join('') // Invertir el número
                        .replace(/(\d{3})(?=\d)/g, '$1.') // Agregar el punto cada 3 dígitos
                        .split('').reverse().join(''); // Volver a invertir

                    // Asignar el valor formateado al campo de entrada
                    input.value = num;
                } else {
                    // Mostrar alerta y limpiar caracteres no numéricos
                    alert("Por favor, introduce un número válido");
                    input.value = input.value.replace(/[^\d]/g, ''); // Limpiar cualquier carácter no numérico
                }
            }
        </script>
    </body>
</x-laravel-ui-adminlte::adminlte-layout>
