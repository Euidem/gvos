<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailTest extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static string $view = 'filament.pages.mail-test';
    protected static ?string $navigationLabel = 'Mail Test';
    protected static ?string $navigationGroup = 'Communications';
    protected static ?int $navigationSort = 3;
    protected static ?string $title = 'Mail Configuration Test';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'to' => auth()->user()?->email ?? '',
            'subject' => 'GVOS Mail Test',
            'body' => 'This is a test email from GVOS. If you received this, your mail configuration is working correctly.',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('to')
                    ->label('Recipient Email')
                    ->email()
                    ->required()
                    ->helperText('Email will be sent to this address. Use your own email for testing.'),

                Forms\Components\TextInput::make('subject')
                    ->label('Subject')
                    ->required()
                    ->maxLength(200),

                Forms\Components\Textarea::make('body')
                    ->label('Message Body')
                    ->rows(5)
                    ->required()
                    ->maxLength(2000),
            ])
            ->statePath('data');
    }

    public function send(): void
    {
        $data = $this->form->getState();

        try {
            Mail::raw($data['body'], function ($message) use ($data) {
                $message
                    ->to($data['to'])
                    ->subject('[GVOS Mail Test] ' . $data['subject']);
            });

            Log::info('GVOS mail test sent', [
                'admin_user_id' => auth()->id(),
                'recipient_hash' => hash('sha256', strtolower($data['to'])),
            ]);

            Notification::make()
                ->title('Mail sent successfully')
                ->success()
                ->body('A test email was sent to ' . $data['to'] . '. Check that address for delivery.')
                ->send();
        } catch (\Throwable $e) {
            $safeError = $this->sanitizeError($e->getMessage());

            Log::warning('GVOS mail test failed', [
                'admin_user_id' => auth()->id(),
                'error' => $safeError,
            ]);

            Notification::make()
                ->title('Mail delivery failed')
                ->danger()
                ->body('The test email could not be sent. Check MAIL_MAILER and SMTP settings in your .env file. Error: ' . $safeError)
                ->persistent()
                ->send();
        }
    }

    private function sanitizeError(string $message): string
    {
        $message = preg_replace('/password[=:\s]+\S+/i', 'password=[redacted]', $message);
        $message = preg_replace('/username[=:\s]+\S+/i', 'username=[redacted]', $message);
        $message = preg_replace('/://[^@\s]+@/', '://[credentials]@', $message);

        return substr($message, 0, 300);
    }

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['super_admin', 'operations_admin']);
    }
}
