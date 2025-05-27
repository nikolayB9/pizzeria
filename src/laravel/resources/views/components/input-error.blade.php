@props(['messages'])

@if($messages)
    @foreach ((array) $messages as $message)
        <div class="text-danger mt-1" style="font-size: 80%;">{{ $message }}</div>
    @endforeach
@endif




