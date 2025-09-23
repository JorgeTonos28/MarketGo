<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'MarketGo') }}</title>
        <style>
            :root {
                color-scheme: light dark;
                --bg: #f8fafc;
                --surface: #ffffff;
                --surface-alt: #0f172a;
                --text: #0f172a;
                --muted: #475569;
                --accent: #22c55e;
                --accent-strong: #16a34a;
                --border: #e2e8f0;
            }

            @media (prefers-color-scheme: dark) {
                :root {
                    --bg: #0f172a;
                    --surface: #111827;
                    --surface-alt: #1e293b;
                    --text: #f8fafc;
                    --muted: #cbd5f5;
                    --accent: #4ade80;
                    --accent-strong: #22c55e;
                    --border: #1f2937;
                }
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                background: var(--bg);
                color: var(--text);
                min-height: 100vh;
            }

            .page {
                max-width: 1200px;
                margin: 0 auto;
                padding: 2.5rem 1.5rem 4rem;
            }

            header {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                gap: 1rem;
                align-items: center;
            }

            .brand {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                font-size: 1.5rem;
                font-weight: 700;
                letter-spacing: -0.02em;
            }

            .brand span {
                display: inline-flex;
                width: 40px;
                height: 40px;
                border-radius: 12px;
                background: var(--accent);
                align-items: center;
                justify-content: center;
                color: #fff;
                font-weight: 700;
                font-size: 1rem;
            }

            .supermarket-selector {
                display: flex;
                gap: 0.75rem;
                align-items: center;
                padding: 0.75rem 1rem;
                background: var(--surface);
                border-radius: 14px;
                border: 1px solid var(--border);
                box-shadow: 0 12px 24px -20px rgba(15, 23, 42, 0.4);
            }

            .supermarket-selector label {
                font-size: 0.9rem;
                color: var(--muted);
            }

            .supermarket-selector select {
                border: 1px solid var(--border);
                border-radius: 12px;
                padding: 0.5rem 1rem;
                font-size: 0.95rem;
                background: var(--surface);
                color: var(--text);
            }

            .supermarket-selector button {
                padding: 0.55rem 1.1rem;
                border-radius: 12px;
                border: none;
                font-weight: 600;
                background: var(--accent);
                color: #fff;
                cursor: pointer;
            }

            main {
                margin-top: 2.5rem;
                display: flex;
                flex-direction: column;
                gap: 2rem;
            }

            .hero {
                display: grid;
                gap: 1.8rem;
                grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
                align-items: stretch;
            }

            .card {
                background: var(--surface);
                border-radius: 18px;
                border: 1px solid var(--border);
                padding: 1.75rem;
                box-shadow: 0 12px 32px -24px rgba(15, 23, 42, 0.4);
            }

            .card h2 {
                margin: 0 0 0.5rem;
                font-size: 1.6rem;
            }

            .card p {
                margin: 0;
                line-height: 1.6;
                color: var(--muted);
            }

            .metrics {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 1rem;
            }

            .metric {
                padding: 1.25rem 1.5rem;
                border-radius: 16px;
                background: linear-gradient(135deg, rgba(34,197,94,0.12), rgba(16,185,129,0.08));
                border: 1px solid rgba(34, 197, 94, 0.18);
            }

            .metric span {
                display: block;
                color: var(--muted);
                font-size: 0.85rem;
            }

            .metric strong {
                display: block;
                margin-top: 0.4rem;
                font-size: 1.8rem;
                font-weight: 700;
            }

            section h3 {
                font-size: 1.3rem;
                margin: 0 0 1rem;
            }

            .shopping-list-items {
                display: grid;
                gap: 0.75rem;
            }

            .shopping-item {
                border: 1px solid var(--border);
                border-radius: 14px;
                padding: 1rem 1.25rem;
                display: grid;
                gap: 0.35rem;
                background: var(--surface);
            }

            .shopping-item-header {
                display: flex;
                justify-content: space-between;
                align-items: baseline;
                gap: 1rem;
            }

            .badge {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                border-radius: 999px;
                padding: 0.25rem 0.75rem;
                font-size: 0.75rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.06em;
            }

            .badge-success {
                background: rgba(34,197,94,0.15);
                color: var(--accent-strong);
            }

            .badge-warning {
                background: rgba(234,179,8,0.18);
                color: #ca8a04;
            }

            .badge-info {
                background: rgba(59,130,246,0.18);
                color: #2563eb;
            }

            .badge-muted {
                background: rgba(148,163,184,0.18);
                color: #475569;
            }

            .shopping-item-footer {
                display: flex;
                flex-wrap: wrap;
                gap: 0.75rem;
                font-size: 0.85rem;
                color: var(--muted);
            }

            .grid-two {
                display: grid;
                gap: 1.5rem;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            }

            .top-products {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 1rem;
            }

            .product-card {
                border-radius: 16px;
                border: 1px solid var(--border);
                padding: 1.2rem 1.4rem;
                background: var(--surface);
                display: grid;
                gap: 0.5rem;
            }

            .product-card strong {
                font-size: 1.05rem;
            }

            .product-card span {
                font-size: 0.85rem;
                color: var(--muted);
            }

            .category-list {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
            }

            .category-card {
                border-radius: 14px;
                background: linear-gradient(135deg, rgba(14,165,233,0.12), rgba(59,130,246,0.08));
                border: 1px solid rgba(59,130,246,0.18);
                padding: 1.25rem;
            }

            .contributions {
                display: grid;
                gap: 0.9rem;
            }

            .contribution {
                border-radius: 12px;
                border: 1px solid var(--border);
                padding: 1rem 1.2rem;
                background: var(--surface);
                display: grid;
                gap: 0.35rem;
            }

            footer {
                margin-top: 3rem;
                color: var(--muted);
                text-align: center;
                font-size: 0.85rem;
            }

            a.button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.45rem;
                background: var(--surface-alt);
                color: #fff;
                padding: 0.65rem 1.1rem;
                border-radius: 12px;
                text-decoration: none;
                font-weight: 600;
            }

            @media (max-width: 640px) {
                .page {
                    padding: 2rem 1rem 3rem;
                }

                .supermarket-selector {
                    width: 100%;
                    justify-content: space-between;
                }
            }
        </style>
    </head>
    <body>
        <div class="page">
            <header>
                <div class="brand">
                    <span>MG</span>
                    <div>
                        MarketGo
                        <div style="font-size:0.9rem;color:var(--muted);font-weight:500;">Tu asistente inteligente para hacer el súper.</div>
                    </div>
                </div>
                @if ($supermarkets->isNotEmpty())
                    <form class="supermarket-selector" method="GET" action="{{ route('home') }}">
                        <label for="supermarket">Supermercado</label>
                        <select name="supermarket" id="supermarket">
                            @foreach ($supermarkets as $supermarket)
                                <option value="{{ $supermarket->id }}" @selected(optional($selectedSupermarket)->id === $supermarket->id)>
                                    {{ $supermarket->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit">Actualizar</button>
                    </form>
                @endif
            </header>

            <main>
                <section class="hero">
                    <div class="card">
                        <h2>Planifica tu compra en minutos</h2>
                        <p>
                            Revisa el catálogo de productos disponible, elige tu supermercado favorito y obtén una lista ordenada
                            por pasillos para que no pierdas tiempo. Marca artículos conforme los agregas al carrito y recibe
                            recordatorios basados en tu historial de consumo.
                        </p>
                        <div style="margin-top:1.5rem; display:flex; gap:0.75rem; flex-wrap:wrap;">
                            <a class="button" href="#lista">Ver lista sugerida</a>
                            <a class="button" style="background:var(--accent);" href="#catalogo">Explorar catálogo</a>
                        </div>
                    </div>
                    <div class="card">
                        <h2>Resumen inteligente</h2>
                        <p>Una mirada rápida a los datos que ya están listos para ayudarte a comprar mejor.</p>
                        <div class="metrics" style="margin-top:1.2rem;">
                            @php
                                $metricLabels = [
                                    'supermarkets' => 'Supermercados',
                                    'products' => 'Productos',
                                    'categories' => 'Categorías',
                                    'activeShoppingLists' => 'Listas activas',
                                    'pendingContributions' => 'Aportes por revisar',
                                    'consumptionLogs' => 'Consumos registrados',
                                ];
                            @endphp
                            @foreach ($metrics as $key => $value)
                                <div class="metric">
                                    <span>{{ $metricLabels[$key] ?? $key }}</span>
                                    <strong>{{ number_format($value) }}</strong>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section id="lista" class="card">
                    <h3>Lista inteligente para {{ optional($selectedSupermarket)->name ?? 'tu supermercado' }}</h3>
                    @if ($shoppingList)
                        <p style="margin:0 0 1rem;color:var(--muted);">
                            {{ $shoppingList->name ?? 'Compra planificada' }} · Presupuesto: ${{ number_format((float) $shoppingList->budget, 2) }} MXN · Estimado actual: ${{ number_format((float) $shoppingList->estimated_total, 2) }} MXN.
                        </p>
                        <div class="shopping-list-items">
                            @foreach ($shoppingList->items as $item)
                                @php
                                    $status = $item->status;
                                    $badgeClass = match ($status) {
                                        'completed' => 'badge-success',
                                        'in_progress' => 'badge-info',
                                        'pending' => 'badge-warning',
                                        default => 'badge-muted',
                                    };
                                    $statusLabel = match ($status) {
                                        'completed' => 'Listo',
                                        'in_progress' => 'En progreso',
                                        'pending' => 'Pendiente',
                                        default => ucfirst($status ?? 'Sin estado'),
                                    };
                                    $sectionName = $item->section?->name ?? 'Pasillo por confirmar';
                                @endphp
                                <article class="shopping-item">
                                    <div class="shopping-item-header">
                                        <div>
                                            <strong>{{ $item->product->name }}</strong>
                                            <div style="font-size:0.9rem;color:var(--muted);">
                                                {{ $item->product->category->name ?? 'Sin categoría' }} · {{ $sectionName }}
                                            </div>
                                        </div>
                                        <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                    </div>
                                    <div class="shopping-item-footer">
                                        <span>Cantidad: <strong style="color:var(--text);">{{ $item->quantity }} {{ $item->quantity_unit }}</strong></span>
                                        @if ($item->estimated_price)
                                            <span>Estimado: <strong style="color:var(--text);">${{ number_format((float) $item->estimated_price, 2) }} MXN</strong></span>
                                        @endif
                                        @if ($item->inventoryItem?->availability_status)
                                            <span>Stock: {{ str_replace('_', ' ', $item->inventoryItem->availability_status) }}</span>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <p style="margin:0;color:var(--muted);">No encontramos una lista asociada a este supermercado aún. Crea una lista nueva desde el panel de administración.</p>
                    @endif
                </section>

                <div class="grid-two">
                    <section id="catalogo" class="card">
                        <h3>Productos más añadidos</h3>
                        <div class="top-products">
                            @forelse ($topProducts as $product)
                                @php
                                    $inventoryForStore = $selectedSupermarket ? $product->inventoryItems->first() : null;
                                @endphp
                                <div class="product-card">
                                    <strong>{{ $product->name }}</strong>
                                    <span>{{ $product->category->name ?? 'Sin categoría' }}</span>
                                    <span>Añadido a listas: {{ $product->shopping_list_items_count }}</span>
                                    @if ($inventoryForStore)
                                        <span>Precio en {{ $selectedSupermarket->brand ?? $selectedSupermarket->name }}: ${{ number_format((float) $inventoryForStore->price, 2) }} MXN</span>
                                        <span>Estado: {{ str_replace('_', ' ', $inventoryForStore->availability_status) }}</span>
                                    @endif
                                </div>
                            @empty
                                <p style="color:var(--muted);">Aún no hay productos con actividad suficiente.</p>
                            @endforelse
                        </div>
                    </section>

                    <section class="card">
                        <h3>Categorías destacadas</h3>
                        <div class="category-list">
                            @foreach ($categoryStats as $category)
                                <div class="category-card">
                                    <strong style="display:block;font-size:1rem;">{{ $category->name }}</strong>
                                    <span style="display:block;font-size:0.85rem;color:var(--muted);">{{ $category->description }}</span>
                                    <span style="display:block;margin-top:0.5rem;font-weight:600;">{{ $category->products_count }} productos</span>
                                </div>
                            @endforeach
                        </div>
                    </section>
                </div>

                <section class="card">
                    <h3>Contribuciones recientes de la comunidad</h3>
                    <div class="contributions">
                        @forelse ($recentContributions as $contribution)
                            @php
                                $status = $contribution->status ?? 'pending';
                                $badgeClass = match ($status) {
                                    'approved' => 'badge-success',
                                    'pending' => 'badge-warning',
                                    'rejected' => 'badge-muted',
                                    default => 'badge-info',
                                };
                                $statusLabel = match ($status) {
                                    'approved' => 'Aprobada',
                                    'pending' => 'Pendiente',
                                    'rejected' => 'Rechazada',
                                    default => ucfirst($status),
                                };
                                $payload = $contribution->payload ?? [];
                            @endphp
                            <article class="contribution">
                                <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;">
                                    <div>
                                        <strong>{{ ucwords(str_replace('_', ' ', $contribution->type)) }}</strong>
                                        <div style="font-size:0.85rem;color:var(--muted);">
                                            {{ $contribution->user?->name ?? 'Usuario anónimo' }} · {{ $contribution->created_at?->diffForHumans() }}
                                        </div>
                                    </div>
                                    <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                </div>
                                @if (! empty($payload))
                                    <div style="font-size:0.85rem;color:var(--muted);">
                                        @foreach ($payload as $key => $value)
                                            <div><strong style="color:var(--text);">{{ ucwords(str_replace('_', ' ', $key)) }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</div>
                                        @endforeach
                                    </div>
                                @endif
                                @if ($contribution->review_notes)
                                    <div style="font-size:0.8rem;color:var(--muted);">Notas: {{ $contribution->review_notes }}</div>
                                @endif
                            </article>
                        @empty
                            <p style="color:var(--muted);">Todavía no hay aportes registrados. Invita a tus usuarios a cargar datos desde la app.</p>
                        @endforelse
                    </div>
                </section>
            </main>

            <footer>
                Construido con Laravel para el MVP de MarketGo. Prueba iniciar sesión con el usuario administrador seed y continúa desarrollando módulos avanzados.
            </footer>
        </div>
    </body>
</html>
