<x-laravel-ui-adminlte::adminlte-layout>

    <body class="hold-transition register-page" style="background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%); font-family: 'Poppins', sans-serif;">

        <style>
            .register-container {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                padding: 20px;
            }

            .register-card {
                display: grid;
                grid-template-columns: 1fr 1fr;
                background: rgba(255, 255, 255, 0.08);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border-radius: 20px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
                overflow: hidden;
                max-width: 900px;
                width: 100%;
                animation: fadeIn 0.8s ease-in-out;
            }

            .register-image {
                background: url('https://source.unsplash.com/600x800/?technology,abstract') center/cover no-repeat;
                position: relative;
            }

            .register-image::after {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.3);
            }

            .register-form {
                padding: 40px;
                color: #fff;
            }

            .register-form .register-logo a {
                font-size: 1.8rem;
                font-weight: bold;
                color: #fff;
                text-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
            }

            .form-control {
                background: rgba(255, 255, 255, 0.2);
                border: none;
                color: #fff;
                border-radius: 10px;
                padding: 12px;
            }

            .form-control::placeholder {
                color: rgba(255, 255, 255, 0.8);
            }

            .form-control:focus {
                box-shadow: 0 0 6px rgba(255, 255, 255, 0.6);
            }

            .input-group-text {
                background: rgba(255, 255, 255, 0.2);
                border: none;
                color: #fff;
                border-radius: 10px;
            }

            .btn-primary {
                background: #00c6ff;
                border: none;
                border-radius: 10px;
                font-weight: bold;
                transition: 0.3s;
            }

            .btn-primary:hover {
                background: #0072ff;
            }

            .text-center a {
                color: #00c6ff;
                text-decoration: none;
                transition: color 0.3s;
            }

            .text-center a:hover {
                color: #fff;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @media (max-width: 768px) {
                .register-card {
                    grid-template-columns: 1fr;
                }
                .register-image {
                    display: none;
                }
            }
        </style>

        <div class="register-container">
            <div class="register-card">
                <!-- Imagen lateral -->
                <div class="register-image"></div>

                <!-- Formulario -->
                <div class="register-form">
                    <div class="register-logo text-center mb-3">
                        <a href="{{ url('/home') }}"><b>{{ config('app.name') }}</b></a>
                    </div>
                    <p class="login-box-msg text-center">Crea tu cuenta</p>

                    <form method="post" action="{{ route('register') }}">
                        @csrf

                        <!-- Nombre completo -->
                        <div class="input-group mb-3">
                            <input type="text" name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" placeholder="Nombre completo">
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-user"></span></div>
                            </div>
                            @error('name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Correo electrónico -->
                        <div class="input-group mb-3">
                            <input type="email" name="email" value="{{ old('email') }}"
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="Correo electrónico">
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                            </div>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Contraseña -->
                        <div class="input-group mb-3">
                            <input type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Contraseña">
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-lock"></span></div>
                            </div>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <!-- Confirmar contraseña -->
                        <div class="input-group mb-3">
                            <input type="password" name="password_confirmation" class="form-control"
                                placeholder="Repite la contraseña">
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-lock"></span></div>
                            </div>
                        </div>

                        <!-- Botón de registro -->
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-block">Registrarse</button>
                            </div>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}">¿Ya tienes una cuenta? Inicia sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </body>
</x-laravel-ui-adminlte::adminlte-layout>
