<x-laravel-ui-adminlte::adminlte-layout>

    <body class="hold-transition login-page" style="background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%); font-family: 'Poppins', sans-serif;">
        <style>
            /* Particles layer */
            #particles-js {
                position: fixed;
                top: 0; left: 0;
                width: 100%; height: 100%;
                z-index: 0;                 /* detrás del contenido */
                pointer-events: none;       /* no bloquear clics */
            }

            .login-box {
                width: 400px;
                margin: 6% auto;
                animation: fadeIn 0.7s ease-in-out;
                position: relative;
                z-index: 1;                 /* encima de partículas */
            }

            .card {
                border-radius: 15px;
                background: rgba(255, 255, 255, 0.08);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }

            .login-card-body {
                border-radius: 15px;
                padding: 30px;
                color: #fff;
            }

            .login-logo a {
                color: #fff !important;
                font-weight: bold;
                font-size: 28px;
                text-shadow: 0px 3px 6px rgba(0, 0, 0, 0.5);
            }

            .login-box-msg {
                font-size: 18px;
                font-weight: 500;
                margin-bottom: 20px;
                color: #e0e0e0;
            }

            .input-group-text {
                background: rgba(255, 255, 255, 0.15);
                border: none;
                color: #fff;
            }

            .btn-primary {
                background: linear-gradient(135deg, #2563eb 0%, #60a5fa 100%);
                border: none;
                border-radius: 10px;
                padding: 10px;
                font-size: 16px;
                font-weight: bold;
                transition: all 0.3s ease-in-out;
            }

            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
            }

            a { color: #93c5fd; text-decoration: none; }
            a:hover { color: #bfdbfe; }

            @keyframes fadeIn {
                0% { opacity: 0; transform: translateY(-20px); }
                100% { opacity: 1; transform: translateY(0); }
            }

            /* Fondo y fuente principal */
            body {
                background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
                font-family: 'Poppins', sans-serif;
                color: #e2e8f0;
            }

            /* Card transparente con efecto glass */
            .login-card-body {
                background: rgba(255, 255, 255, 0.08);
                border-radius: 15px;
                backdrop-filter: blur(10px);
                color: #f8fafc;
            }

            /* Inputs */
            .form-control {
                background: transparent !important;
                border: none;
                border-bottom: 2px solid rgba(255, 255, 255, 0.5);
                border-radius: 0;
                padding: 10px 5px;
                font-size: 15px;
                color: #ffffff;
                transition: border-color 0.3s ease;
            }
            .form-control:focus {
                border-bottom: 2px solid #4da3ff;
                outline: none;
                color: #ffffff;
            }
            .form-control::placeholder {
                color: rgba(255, 255, 255, 0.8);
                font-weight: 500;
            }

            /* Íconos */
            .input-group-text {
                background: transparent;
                border: none;
                color: #a7c7ff;
            }

            /* Botón */
            .btn-primary {
                background: linear-gradient(135deg, #0072ff 0%, #00c6ff 100%);
                border: none;
                border-radius: 8px;
                padding: 10px;
                font-size: 16px;
                font-weight: 600;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0, 114, 255, 0.4);
            }

            /* Evitar estilos feos de autocomplete */
            input:-webkit-autofill,
            input:-webkit-autofill:hover,
            input:-webkit-autofill:focus,
            input:-webkit-autofill:active {
                -webkit-box-shadow: 0 0 0 30px rgba(0, 0, 0, 0) inset !important;
                -webkit-text-fill-color: #ffffff !important;
                background: transparent !important;
                transition: background-color 5000s ease-in-out 0s;
            }
        </style>

        <!-- Capa de partículas -->
        <div id="particles-js"></div>

        <div class="login-box">
            <div class="login-logo">
                <a href="{{ url('/home') }}">{{ config('app.name') }}</a>
            </div>

            <div class="card">
                <div class="card-body login-card-body">
                    <p class="login-box-msg">Inicia sesión para continuar</p>

                    <form method="POST" action="{{ url('/login') }}">
                        @csrf

                        <!-- Email -->
                        <div class="input-group mb-3">
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="Correo electrónico"
                                class="form-control @error('email') is-invalid @enderror">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </div>
                            </div>
                            @error('email')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="input-group mb-3">
                            <input type="password" name="password" placeholder="Contraseña"
                                class="form-control @error('password') is-invalid @enderror">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                            @error('password')
                                <span class="error invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Botón -->
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
                            </div>
                        </div>
                    </form>

                    <p class="mb-0 mt-3">
                        <a href="{{ route('register') }}">Crear una cuenta</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- particles.js (CDN) -->
        <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                /* Config elegante acorde a tu paleta */
                particlesJS('particles-js', {
                    particles: {
                        number: { value: 120, density: { enable: true, value_area: 900 } },
                        color: { value: '#93c5fd' },
                        shape: { type: 'circle' },
                        opacity: { value: 0.25 },
                        size: { value: 3, random: true },
                        line_linked: {
                            enable: true,
                            distance: 140,
                            color: '#93c5fd',
                            opacity: 0.25,
                            width: 1
                        },
                        move: {
                            enable: true,
                            speed: 1.2,
                            direction: 'none',
                            random: false,
                            straight: false,
                            out_mode: 'out'
                        }
                    },
                    interactivity: {
                        detect_on: 'canvas',
                        events: {
                            onhover: { enable: true, mode: 'repulse' },
                            onclick: { enable: false, mode: 'push' },
                            resize: true
                        },
                        modes: {
                            repulse: { distance: 100, duration: 0.4 }
                        }
                    },
                    retina_detect: true
                });
            });
        </script>
    </body>
</x-laravel-ui-adminlte::adminlte-layout>
