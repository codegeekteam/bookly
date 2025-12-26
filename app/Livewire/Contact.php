<?php

namespace App\Livewire;

use Filament\Notifications\Notification;
use Livewire\Component;

class Contact extends Component
{
    public $name;
    public $email;
    public $phone;
    public $message;

    public function rules()
    {
        return [
            'name' => 'required|regex:/^[a-zA-Z\'\- ]+$/',
            'email' => 'email',
            'phone' => 'required|min:10|regex:/^01[0125]\d{8}$/',
            'message' => 'required',
        ];
    }


    public function submit()
    {
        $this->validate();
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->message = '';


    }

    public function render()
    {
        return view('livewire.contact');
    }
}
