<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;

class ManageSiteSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Website Settings';
    protected static string $view = 'filament.pages.manage-site-settings';

    // ⚡ KUNCI: Hanya Super Admin yang bisa melihat menu pengaturan ini
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'super_admin';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $setting = SiteSetting::first() ?? SiteSetting::create();
        $this->form->fill($setting->toArray());
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Company & System Identity')->schema([
                TextInput::make('site_name')->label('Website / Company Name')->required(),
                FileUpload::make('logo_path')->label('Company Logo')->image()->directory('site-logos'),
            ])
        ])->statePath('data');
    }

    public function save(): void
    {
        $setting = SiteSetting::first();
        $setting->update($this->form->getState());

        Notification::make()->title('Settings updated successfully!').success()->send();
    }
}