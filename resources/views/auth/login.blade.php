<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <title>Página de inicio</title>
</head>
<body>
<header>
    <img src="{{ asset('img/Logo_Gobierno.png') }}" alt="logo">
    <h1>Página de inicio</h1>
</header>
<section class="big-container">
    <div class="container">
        <div class="screen">
            <div class="screen__content">
                <form class="login" method="POST" action="{{ route('login') }}">
                    @csrf  {{-- Laravel CSRF Protection --}}

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="login__field">
                        <i class="login__icon fas fa-user"></i>
                        <input
                            type="text"
                            class="login__input @error('dui') is-invalid @enderror"
                            placeholder="DUI (00000000-0)"
                            name="dui"
                            id="dui"
                            value="{{ old('dui') }}"
                            required
                            autofocus
                            pattern="[0-9]{8}-[0-9]"
                        >
                        @error('dui')
                        <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="login__field">
                        <i class="login__icon fas fa-lock"></i>
                        <input
                            type="password"
                            class="login__input @error('password') is-invalid @enderror"
                            placeholder="Contraseña"
                            name="password"
                            id="password"
                            required
                        >
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="login__field">
                        <label class="remember-me">
                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span>Recordar usuario</span>
                        </label>
                    </div>

                    <button class="button login__submit">
                        <span class="button__text">Iniciar Sesión</span>
                        <i class="button__icon fas fa-chevron-right"></i>
                    </button>

                    @if (Route::has('password.request'))
                        <div class="forgot-password">
                            <a href="{{ route('password.request') }}">
                            </a>
                        </div>
                    @endif
                </form>
                <div class="social-login">
                </div>
            </div>
            <div class="screen__background">
                <span class="screen__background__shape screen__background__shape4"></span>
                <span class="screen__background__shape screen__background__shape3"></span>
                <span class="screen__background__shape screen__background__shape2"></span>
                <span class="screen__background__shape screen__background__shape1"></span>
            </div>
        </div>
    </div>
</section>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- Optional: Add DUI formatting JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const duiInput = document.getElementById('dui');

        duiInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digits

            if (value.length >= 8) {
                value = value.substring(0, 8) + '-' + value.substring(8, 9);
            }

            e.target.value = value;
        });
    });
</script>

</body>
</html>
