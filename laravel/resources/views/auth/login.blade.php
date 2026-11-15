<x-guest-layout>
    <section class="auth-shell">
        <div class="auth-login-grid">
            <div class="auth-form-panel">
                <div class="auth-logo">
                    <img src="{{ asset(logoEmpresaConfig()) }}" alt="{{ nombreEmpresaConfig() }}">
                </div>

                <div class="auth-form-header">
                    <h2>Iniciar sesion</h2>
                    <p>{{ nombreEmpresaConfig() }}</p>
                </div>

                <x-jet-validation-errors class="mb-4" />

                @if (session('status'))
                    <div class="mb-4 font-medium text-sm text-green-600">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="auth-form">
                    @csrf

                    <div>
                        <x-jet-label for="email" value="Correo electronico" />
                        <x-jet-input id="email" class="block mt-2 w-full" type="email" name="email" :value="old('email')" required autofocus />
                    </div>

                    <div class="mt-4">
                        <x-jet-label for="password" value="Contrasena" />
                        <x-jet-input id="password" class="block mt-2 w-full" type="password" name="password" required autocomplete="current-password" />
                    </div>

                    <div class="auth-form-actions">
                        <x-jet-button class="auth-submit-button">
                            Entrar al sistema
                        </x-jet-button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</x-guest-layout>
