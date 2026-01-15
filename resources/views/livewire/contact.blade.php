<div class="wrapper">
    <h2>Contact us</h2>

    <form wire:click="submit" id="myform">
        <div class="input_field">
            <input type="text" wire:model="name" placeholder="Name" id="name">
            @error('name') <span style="color: red">{{ $message }}</span> @enderror
        </div>
        <div class="input_field">
            <input type="text" wire:model="phone" placeholder="Phone" id="phone">
            @error('phone') <span style="color: red">{{ $message }}</span> @enderror
        </div>
        <div class="input_field">
            <input type="text" wire:model="email" placeholder="Email" id="email">
            @error('email') <span style="color: red">{{ $message }}</span> @enderror
        </div>
        <div class="input_field">
            <textarea wire:model="message" placeholder="Message" id="message"></textarea>
            @error('message') <span style="color: red">{{ $message }}</span> @enderror
        </div>
        <div class="btn">
            <input type="submit">
        </div>
    </form>
</div>
