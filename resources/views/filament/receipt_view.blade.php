@if ($getRecord() && $getRecord()->receipt_path)
    <a href="{{ Storage::disk('public')->url($getRecord()->receipt_path) }}"
       target="_blank">
        <img src="{{ Storage::disk('public')->url($getRecord()->receipt_path) }}"
             alt="Receipt"
             style="max-width: 250px; cursor: pointer;"
             class="rounded shadow-lg border">
    </a>
@endif
