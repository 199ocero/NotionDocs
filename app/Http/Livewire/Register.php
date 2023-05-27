<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Filament\Facades\Filament;
use Illuminate\Auth\Events\Registered;
use JeffGreco13\FilamentBreezy\Http\Livewire\Auth\Register as FilamentBreezyRegister;

class Register extends FilamentBreezyRegister
{
    // Optionally, you can override the entire register() method to customize exactly what happens at registration
    public function register()
    {
        $preparedData = $this->prepareModelData($this->form->getState());

        $user = config('filament-breezy.user_model')::create($preparedData);

        // assign role admin for new user
        $user->assignRole('admin');
        
        event(new Registered($user));
        Filament::auth()->login($user, true);

        return redirect()->to(config('filament-breezy.registration_redirect_url'));
    }
}
