@extends('layouts.app')

@section('content')

<div class="container py-3">

    {{-- TÍTULO --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0">Painel Financeiro</h3>

        <a href="{{ route('transactions.index') }}" class="btn btn-outline-primary">
            📊 Ver extrato completo
        </a>
    </div>

    <div class="row g-3">

        {{-- ===========================
             COLUNA DIREITA
        ============================ --}}
        <div class="col-12 col-lg-6">

            {{-- CARD USUÁRIO + SALDO --}}
            <div class="card shadow-sm">
                <div class="card-body" style="max-height: 180px;">

                    {{-- Usuário --}}
                    <div class="mb-2">
                        <h6 class="text-muted mb-1 small">Usuário logado:</h6>
                        <h5 class="fw-bold mb-0 text-break" style="font-size: 1.15rem;">
                            {{ auth()->user()->name }}
                            <span class="text-secondary small">(#{{ auth()->id() }})</span>
                        </h5>
                    </div>

                    <hr class="my-3">

                    {{-- Saldo --}}
                    <div class="mt-2">
                        <span class="text-dark small fw-semibold" style="letter-spacing: .8px;">
                            SEU SALDO
                        </span>

                        <div class="d-flex align-items-baseline mt-1">
                            <span style="font-size: 1.2rem; margin-right: 4px; color: #198754;">R$</span>

                            <span id="saldo" class="fw-bold"
                                style="font-size: 2rem; color: #198754; line-height: 1;">
                                {{ number_format($wallet->balance, 2, ',', '.') }}
                            </span>
                        </div>
                    </div>

                </div>
            </div>

            {{-- DEPÓSITO --}}
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white fw-semibold">➕ Depósito</div>

                <div class="card-body" style="max-height: 180px;">
                    <form action="{{ route('deposit') }}" method="POST" class="row g-2">
                        @csrf

                        <div class="col-12">
                            <input type="number" step="0.01" name="amount" class="form-control"
                                style="height: 45px;" placeholder="Valor do depósito" required>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-success w-100 py-2">
                                Depositar
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            {{-- TRANSFERÊNCIA --}}
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white fw-semibold">🔁 Transferência</div>

                <div class="card-body" style="max-height: 180px;">

                    <form action="{{ route('transfer') }}" method="POST" class="row g-2">
                        @csrf

                        <select name="to_user_id" id="to_user_id" class="form-select select2" required>
                            <option value="">Selecione um usuário...</option>

                            @foreach($users as $u)
                                <option value="{{ $u->id }}">
                                    {{ $u->name }} ({{ $u->email }})
                                </option>
                            @endforeach
                        </select>

                        <div class="col-12">
                            <input type="number" step="0.01" name="amount" class="form-control"
                                style="height: 45px;" placeholder="Valor da transferência" required>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                Transferir
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div> {{-- FIM COL DIREITA --}}



        {{-- ===========================
             COLUNA ESQUERDA — TIMELINE
        ============================ --}}
        <div class="col-12 col-lg-6">
            @include('components.timeline', ['transactions' => $transactions])
        </div>

    </div>



    {{-- ===========================
         HISTÓRICO COMPLETO
    ============================ --}}
    <div class="row mt-4">
        <div class="col-12">

            <div class="card shadow-sm">
                <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                    <span>📜 Histórico de Movimentações</span>

                    {{-- SEARCH --}}
                    <div style="max-width: 240px;">
                        <input type="text" id="searchHistory" class="form-control form-control-sm"
                            placeholder="Buscar... (valor, tipo, usuário)">
                    </div>
                </div>

                <div class="card-body p-2" id="historico">

                    @foreach($transactions as $t)
                        <div class="history-item border rounded p-3 mb-2" id="tx-{{ $t->id }}">

                            <div class="d-flex justify-content-between mb-1">
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

                            {{-- TRANSFERÊNCIA --}}
                            @if($t->type === 'transfer')
                                <div class="small">
                                    <strong>De:</strong>
                                    {{ $t->fromUser->name }} ({{ $t->fromUser->email }})

                                    <br>

                                    <strong>Para:</strong>
                                    {{ $t->toUser->name }} ({{ $t->toUser->email }})
                                </div>

                            {{-- DEPÓSITO --}}
                            @else
                                <div class="small">
                                    <strong>Para:</strong>
                                    {{ $t->toUser->name }} ({{ $t->toUser->email }})
                                </div>
                            @endif

                            <div class="text-muted small mt-1">
                                {{ $t->created_at->diffForHumans() }}
                            </div>

                            @if($t->status === 'completed')
                                <form class="form-reverter mt-2" data-id="{{ $t->id }}">
                                    @csrf
                                    <button class="btn btn-outline-danger btn-sm w-100">
                                        Reverter Transação
                                    </button>
                                </form>
                            @endif

                        </div>
                    @endforeach

                </div>

            </div>

        </div>
    </div>

</div>



{{-- ======================
       AJAX
====================== --}}
<script>
    document.addEventListener("DOMContentLoaded", () => {

        function formatar(v) { return Number(v).toFixed(2).replace('.', ','); }
        function atualizarSaldo(v) { document.querySelector("#saldo").innerHTML = "R$ " + formatar(v); }
        function limparForm(f) { f.reset(); }

        // TOAST
        function mostrarMensagem(msg, tipo = "success") {
            const box = document.querySelector("#toast-container");
            const div = document.createElement("div");
            div.className = `toast-msg toast-${tipo}`;
            div.innerHTML = msg;
            box.appendChild(div);

            setTimeout(() => div.classList.add("show"), 20);
            setTimeout(() => {
                div.classList.remove("show");
                setTimeout(() => div.remove(), 300);
            }, 3000);
        }

        // ----------------- DEPÓSITO
        document
            .querySelector("form[action='{{ route('deposit') }}']")
            .addEventListener("submit", async e => {

                e.preventDefault();
                const form = e.target;
                const data = new FormData(form);

                const res = await fetch(form.action, {
                    method: "POST",
                    body: data,
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                });

                const json = await res.json();

                if (json.success) {
                    atualizarSaldo(json.saldo);
                    limparForm(form);
                    mostrarMensagem(json.message);
                } else {
                    mostrarMensagem(json.error, "danger");
                }
            });

        // ----------------- TRANSFERÊNCIA
        document
            .querySelector("form[action='{{ route('transfer') }}']")
            .addEventListener("submit", async e => {

                e.preventDefault();
                const form = e.target;
                const data = new FormData(form);

                const res = await fetch(form.action, {
                    method: "POST",
                    body: data,
                    headers: { "X-Requested-With": "XMLHttpRequest" }
                });

                const json = await res.json();

                if (json.success) {
                    atualizarSaldo(json.saldo);
                    limparForm(form);
                    mostrarMensagem(json.message);
                } else {
                    mostrarMensagem(json.error, "danger");
                }
            });

        // ----------------- REVERSÃO
        document.addEventListener("submit", async e => {

            if (!e.target.matches(".form-reverter")) return;
            e.preventDefault();

            const id = e.target.dataset.id;

            const res = await fetch(`/reverse/${id}`, {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                }
            });

            const json = await res.json();

            if (json.success) {
                atualizarSaldo(json.saldo);
                document.querySelector(`#tx-${id}`).remove();
                mostrarMensagem(json.message);
            } else {
                mostrarMensagem(json.error, "danger");
            }
        });

    });

    // ------------------------
    // FILTRO DE BUSCA
    // ------------------------
    document.getElementById('searchHistory').addEventListener('keyup', function () {

        let term = this.value.toLowerCase().trim();
        let items = document.querySelectorAll('.history-item');

        items.forEach(item => {
            let text = item.innerText.toLowerCase();
            item.style.display = text.includes(term) ? '' : 'none';
        });
    });
</script>

@endsection
