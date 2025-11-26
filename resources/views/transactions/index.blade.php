@extends('layouts.app')

@section('content')

    <div class="container py-3">

        {{-- TÍTULO + BOTÃO --}}
        <div class="d-flex justify-content-between align-items-center mb-4">

            <h2 class="fw-bold m-0">📊 Extrato de Transações</h2>

            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                ← Voltar ao painel
            </a>

        </div>

        {{-- FILTROS --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                🔎 Filtros
            </div>

            <div class="card-body">

                <div class="row g-3">

                    {{-- PERÍODO --}}
                    <div class="col-12 col-md-4">
                        <label class="fw-semibold">Período</label>
                        <select id="period" class="form-select">
                            <option value="">Selecione</option>
                            <option value="today">Hoje</option>
                            <option value="week">Esta semana</option>
                            <option value="month">Este mês</option>
                            <option value="custom">Personalizado</option>
                        </select>
                    </div>

                    {{-- CUSTOM DATE --}}
                    <div class="col-12 col-md-4 custom-date d-none">
                        <label class="fw-semibold">Data inicial</label>
                        <input type="date" id="start_date" class="form-control">
                    </div>

                    <div class="col-12 col-md-4 custom-date d-none">
                        <label class="fw-semibold">Data final</label>
                        <input type="date" id="end_date" class="form-control">
                    </div>

                    {{-- TYPE --}}
                    <div class="col-12 col-md-3">
                        <label class="fw-semibold">Tipo</label>
                        <select id="type" class="form-select">
                            <option value="">Todos</option>
                            <option value="deposit">Depósito</option>
                            <option value="transfer">Transferência</option>
                        </select>
                    </div>

                    {{-- STATUS --}}
                    <div class="col-12 col-md-3">
                        <label class="fw-semibold">Status</label>
                        <select id="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="completed">Concluído</option>
                            <option value="reversed">Revertido</option>
                        </select>
                    </div>

                    {{-- BOTÃO --}}
                    <div class="col-12 col-md-3">
                        <label>&nbsp;</label>
                        <button id="filterButton" class="btn btn-primary w-100">
                            Aplicar filtros
                        </button>
                    </div>

                </div>

            </div>
        </div>

        {{-- RESULTADOS --}}
        <div class="card shadow-sm mb-5">
            <div class="card-header bg-white fw-semibold">📄 Resultados</div>

            <div class="card-body" id="results">
                <p class="text-muted">Nenhum filtro aplicado ainda.</p>
            </div>
        </div>

    </div>

    {{-- ======================= JS ======================= --}}
    <script>
        function formatMoney(value) {
            return Number(value).toFixed(2).replace('.', ',');
        }

        // Mostrar campos de datas personalizadas
        document.querySelector("#period").addEventListener("change", () => {
            const isCustom = document.querySelector("#period").value === "custom";
            document.querySelectorAll(".custom-date").forEach(div => {
                div.classList.toggle("d-none", !isCustom);
            });
        });

        // Botão de filtros
        document.querySelector("#filterButton").addEventListener("click", async () => {

            const params = new URLSearchParams({
                period: document.querySelector("#period").value,
                start_date: document.querySelector("#start_date").value,
                end_date: document.querySelector("#end_date").value,
                type: document.querySelector("#type").value,
                status: document.querySelector("#status").value,
            });

            const response = await fetch("{{ route('transactions.filter') }}?" + params.toString(), {
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });

            const data = await response.json();
            let html = "";

            if (data.length === 0) {
                html = `<p class="text-center text-muted">Nenhum resultado encontrado.</p>`;
            }

            data.forEach(t => {

                let badgeClass =
                    t.status === "completed" ? "bg-success" :
                        t.status === "reversed" ? "bg-danger" :
                            "bg-secondary";

                html += `
                    <div class="border rounded p-3 mb-2">

                        <div class="d-flex justify-content-between">
                            <strong>${t.type.toUpperCase()}</strong>
                            <span class="badge ${badgeClass}">${t.status}</span>
                        </div>

                        <div><strong>Valor:</strong> R$ ${formatMoney(t.amount)}</div>

                        ${t.type === "transfer"
                        ? `<div><strong>De:</strong> ${t.from_user?.name ?? "N/A"} (${t.from_user?.email ?? "N/A"}) 
                                <br><strong>Para:</strong> ${t.to_user?.name ?? "N/A"} (${t.to_user?.email ?? "N/A"})</div>`
                        : `<div><strong>Recebido por:</strong> ${t.to_user?.name ?? "N/A"} (${t.to_user?.email ?? "N/A"})</div>`}

                        <div class="text-muted small mt-1">
                            ${new Date(t.created_at).toLocaleString('pt-BR')}
                        </div>

                    </div>
                `;
            });

            document.querySelector("#results").innerHTML = html;
        });
    </script>

@endsection