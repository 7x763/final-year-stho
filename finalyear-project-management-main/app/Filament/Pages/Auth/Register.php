<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Schemas\Components\Component;

class Register extends BaseRegister
{
    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->autocomplete('new-password');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return parent::getPasswordConfirmationFormComponent()
            ->autocomplete('new-password');
    }
}
