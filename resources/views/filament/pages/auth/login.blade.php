<x-filament-panels::page.simple>
    @if (filament()->hasRegistration())
    <x-slot name="subheading">
        {{ __('filament-panels::pages/auth/login.actions.register.before') }}

        {{ $this->registerAction }}
    </x-slot>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
    scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form id="form" wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()" />
    </x-filament-panels::form>

    <div class="text-center">
        {{ __('models.common.or_login_with') }}
    </div>

    <div class="flex items-center justify-center space-x-2">
        <x-filament::button href="/auth/google" tag="a" color="danger" icon="phosphor-google-logo-bold" outlined>
            Google
        </x-filament::button>
        <x-filament::button href="#" tag="a" color="gray" icon="phosphor-facebook-logo-bold" outlined>
            Facebook
        </x-filament::button>
        <x-filament::button href="#" tag="a" color="gray" icon="phosphor-x-logo-bold" outlined>
            X
        </x-filament::button>
    </div>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
    scopes: $this->getRenderHookScopes()) }}
</x-filament-panels::page.simple>
