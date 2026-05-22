<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                TextInput::make('no_hp')
                    ->label('Nomor HP')
                    ->tel()
                    ->placeholder('Contoh: 08123456789')
                    ->maxLength(15),
                FileUpload::make('foto')
                    ->label('Foto Profil')
                    ->image()
                    ->disk('public')
                    ->directory('profile-photos')
                    ->avatar()
                    ->maxSize(1024)
                    ->imageEditor(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}
