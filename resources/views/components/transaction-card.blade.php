<div class="border rounded p-3 mb-2" id="tx-{{ $t->id }}">

    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong class="text-uppercase">{{ $t->type }}</strong>

        <span class="badge 
            @if($t->status == 'completed') bg-success
            @elseif($t->status == 'pending') bg-warning text-dark
            @else bg-secondary
            @endif">
            {{ $t->status }}
        </span>
    </div>

    <div class="small mb-1">
        <strong>Valor:</strong>
        R$ {{ number_format($t->amount, 2, ',', '.') }}
    </div>

    @if($t->type === 'transfer')
        <div class="small">
            <strong>De:</strong> {{ $t->toUser->name }} → <strong>Para:</strong> {{ $t->to_user_id }}
        </div>
    @else
        <div class="small">
            <strong>Para:</strong> {{ $t->to_user_id }}
        </div>
    @endif

    @if($t->status === 'completed')
        <form class="form-reverter mt-2" data-id="{{ $t->id }}">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                Reverter Transação
            </button>
        </form>
    @endif

</div>