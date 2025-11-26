<div class="card shadow-sm mt-0">
    <div class="card-header bg-white fw-semibold">
        📝 Atividades Recentes
    </div>

    <div class="card-body" style="max-height: 550px; overflow-y: auto;">

        @if($transactions->isEmpty())
            <p class="text-muted text-center">Nenhuma atividade recente.</p>
        @endif

        <ul class="timeline list-unstyled m-0 p-0">

            @foreach($transactions as $t)

                <li class="timeline-item mb-4 d-flex">

                    {{-- Bolinha --}}
                    <div class="timeline-marker me-3"></div>

                    <div class="fw-semibold">

                        {{-- TRANSFERÊNCIA --}}
                        @if($t->type === 'transfer')

                            @php
                                $from = $t->fromUser;
                                $to = $t->toUser;
                            @endphp

                            {{-- Você enviou --}}
                            @if($t->from_user_id == auth()->id())
                                Você enviou
                                <span class="text-primary">
                                    R$ {{ number_format($t->amount, 2, ',', '.') }}
                                </span>
                                para
                                <strong>{{ $to->name ?? 'Usuário removido' }}</strong>
                                <span class="text-muted small">({{ $to->email ?? 'N/A' }})</span>

                                {{-- Você recebeu --}}
                            @else
                                Você recebeu
                                <span class="text-success">
                                    R$ {{ number_format($t->amount, 2, ',', '.') }}
                                </span>
                                de
                                <strong>{{ $from->name ?? 'Usuário removido' }}</strong>
                                <span class="text-muted small">({{ $from->email ?? 'N/A' }})</span>
                            @endif


                            {{-- DEPÓSITO --}}
                        @elseif($t->type === 'deposit')

                            Depósito de
                            <span class="text-success">
                                R$ {{ number_format($t->amount, 2, ',', '.') }}
                            </span>

                            {{-- REVERTIDA --}}
                        @elseif($t->status === 'reversed')
                            <span class="text-danger">
                                Transação revertida
                            </span>
                        @endif

                    </div>
                </li>

            @endforeach
        </ul>

    </div>
</div>