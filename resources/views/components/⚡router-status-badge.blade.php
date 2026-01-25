<?php

use function Livewire\Volt\{state, on}; // If using Volt SFCs
// OR standard SFC logic:
use App\Models\Router;
use Livewire\Volt\Component;

new class extends Component {
    public function with()
    {
        return [
            'count' => Router::where('is_online', false)->count(),
        ];
    }
}; ?>

<div >
    @if($count > 0)
        <span class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full shadow-sm animate-pulse">
            {{ $count }}
        </span>
    @endif
</div>
