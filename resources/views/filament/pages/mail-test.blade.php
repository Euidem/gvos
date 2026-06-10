<x-filament-panels::page>

    <div class="max-w-2xl">

        <div class="mb-6 rounded-lg border border-primary-200 bg-primary-50 p-4 text-sm text-primary-800 dark:border-primary-700 dark:bg-primary-900/20 dark:text-primary-300">
            <strong>Mail configuration test.</strong>
            This tool sends a real email via your configured MAIL_MAILER.
            Use your own address. Errors are logged — credentials are never exposed in error output.
        </div>

        <form wire:submit="send">
            {{ $this->form }}

            <div class="mt-6">
                <x-filament::button type="submit" size="lg" wire:loading.attr="disabled">
                    <span wire:loading.remove>Send Test Email</span>
                    <span wire:loading>Sending…</span>
                </x-filament::button>
            </div>
        </form>

    </div>

    <x-filament-actions::modals />

</x-filament-panels::page>
