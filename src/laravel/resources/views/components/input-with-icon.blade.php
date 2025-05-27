@props(['type' => 'text', 'name', 'value' => old($name), 'placeholder', 'icon', 'messages' => null])

<div class="form-group">
    <div class="input-group">
        <input type="{{ $type }}"
               class="form-control {{ !empty($messages) ? 'is-invalid' : '' }}"
               name="{{ $name }}"
               value="{{ $value }}"
               placeholder="{{ $placeholder }}"
            {{ $attributes }} >
        <div class="input-group-append">
            <div class="input-group-text">
                <span class="{{ $icon }}"></span>
            </div>
        </div>
    </div>
    <x-input-error :messages="$messages"/>
</div>
