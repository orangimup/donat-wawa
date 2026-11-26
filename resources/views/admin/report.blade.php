@php
    $money = fn ($amount) => 'Rp ' . number_format((int) $amount, 0, ',', '.');
    $statusLabels = [
        'waiting' => __('Waiting'),
        'process' => __('Process'),
        'shipped' => __('Shipped'),
        'done' => __('Done'),
        'rejected' => __('Rejected'),
    ];
    $statusColors = [
        'waiting' => '#d97706',
        'process' => '#1d62c9',
        'shipped' => '#7a3fb0',
        'done' => '#2ea44f',
        'rejected' => '#b3261e',
    ];
    $topQty = max(1, (int) $products->max('quantity'));
    $img = function (string $file) {
        $path = public_path('assets/images/' . $file);
        return file_exists($path) ? 'data:image/png;base64,' . base64_encode(file_get_contents($path)) : null;
    };
    $logo = $img('logo-full.png');
    $logoLight = $img('logo-full-putih.png');
    $font = fn (string $file) => str_replace('\\', '/', public_path('fonts/' . $file));
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <title>{{ __('Sales Report') }} - {{ $period }}</title>
    <style>
        @font-face { font-family: 'Jakarta'; font-weight: normal; src: url('{{ $font('PlusJakartaSans-Regular.ttf') }}') format('truetype'); }
        @font-face { font-family: 'Jakarta'; font-weight: bold; src: url('{{ $font('PlusJakartaSans-Bold.ttf') }}') format('truetype'); }
        @font-face { font-family: 'Bricolage'; font-weight: normal; src: url('{{ $font('BricolageGrotesque-ExtraBold.ttf') }}') format('truetype'); }
        @font-face { font-family: 'Bricolage'; font-weight: bold; src: url('{{ $font('BricolageGrotesque-ExtraBold.ttf') }}') format('truetype'); }
        /* DESAIN 1 — Hero Card */
        @page { margin: 30px 38px 54px 38px; }
        body { margin: 0; font-family: 'Jakarta', DejaVu Sans, sans-serif; font-size: 9.5px; color: #4e342e; }
        table { width: 100%; border-collapse: collapse; }
        .right { text-align: right; } .center { text-align: center; }
        .muted { color: #897c74; } .strong { font-weight: bold; }

        .logo { height: 46px; }
        .eyebrow { font-size: 7.5px; font-weight: bold; letter-spacing: 3px; color: #7b2026; }
        .printed { margin-top: 4px; font-size: 7.5px; color: #897c74; }

        .hero { position: relative; overflow: hidden; margin-top: 16px; background: #7b2026; border-radius: 18px; padding: 16px 26px; }
        .orb { position: absolute; border-radius: 120px; }
        .hero-in { position: relative; }
        .hero td { vertical-align: middle; }
        .chip { display: inline-block; padding: 3px 11px; border-radius: 10px; background: #fbbf24; color: #4a1216; font-size: 7.5px; font-weight: bold; letter-spacing: 1.5px; }
        .period { margin-top: 8px; font-family: 'Bricolage', sans-serif; font-size: 26px; line-height: 1; font-weight: bold; color: #fff9f0; }
        .rev-label { font-size: 8.5px; line-height: 1; font-weight: bold; letter-spacing: 1.5px; color: #ffffff; }
        .rev { margin-top: 5px; font-family: 'Bricolage', sans-serif; font-size: 27px; line-height: 1; font-weight: bold; color: #ffffff; }
        .cmp { display: inline-block; padding: 4px 12px; border-radius: 9px; background: rgba(255, 255, 255, 0.17); border: 1px solid rgba(255, 255, 255, 0.35); font-size: 9px; font-weight: bold; color: #ffffff; }
        .stats { border-collapse: separate; border-spacing: 8px 0; width: 104%; margin-left: -8px; }
        .stat { width: 25%; background: rgba(255, 255, 255, 0.17); border: 1px solid rgba(255, 255, 255, 0.35); border-top: 1px solid rgba(255, 255, 255, 0.6); border-radius: 14px; padding: 12px 16px; }
        .stat-val { font-family: 'Bricolage', sans-serif; font-size: 28px; font-weight: bold; color: #ffffff; }
        .stat-label { margin-top: 5px; font-size: 9.5px; font-weight: bold; color: #ffffff; }




        .mon { margin-left: 8px; font-family: 'Bricolage', sans-serif; font-size: 17px; font-weight: bold; color: #fff9f0; }
        .hline { height: 1px; background: #a04a50; margin: 14px 0 14px; }
        .strip td { vertical-align: middle; }
        .strip .srev { width: 32%; }
        .strip .sc { padding-left: 20px; border-left: 1px solid #a04a50; }
        .strip .sc .stat-val { font-size: 32px; line-height: 1; }
        .strip .sc .stat-label { margin-top: 7px; font-size: 12px; font-weight: bold; color: #ffffff; }
        .strip .srev .rev-label { font-size: 10px; margin-bottom: 2px; }

        .h2 { margin: 24px 0 8px; font-family: 'Bricolage', sans-serif; font-size: 14px; font-weight: bold; color: #4e342e; }

        .data th { padding: 8px 6px; font-size: 8px; font-weight: bold; letter-spacing: 1.2px; text-transform: uppercase; color: #897c74; text-align: left; border-bottom: 1.5px solid #7b2026; }
        .data th.right { text-align: right; } .data th.center { text-align: center; }
        .data td { padding: 8px 6px; border-bottom: 1px solid #efe6e0; vertical-align: middle; }
        .data tr { page-break-inside: avoid; }
        .code { font-weight: bold; color: #7b2026; }
        .dot { font-size: 9px; }
        .bar-bg { background: #f6ece4; height: 6px; border-radius: 3px; }
        .bar { background: #7b2026; height: 6px; border-radius: 3px; }
        .empty { padding: 14px; text-align: center; color: #897c74; }

        .footer { position: fixed; bottom: -38px; left: 0; right: 0; font-size: 7.5px; color: #897c74; border-top: 1px solid #efe6e0; padding-top: 7px; }
        .pagenum:before { content: counter(page); }
    </style>
</head>

<body>
    <div class="footer">
        <table><tr>
            <td>Donat Wawa &middot; {{ __('Sales Report') }} {{ $period }}</td>
            <td class="right">{{ __('Page') }} <span class="pagenum"></span></td>
        </tr></table>
    </div>

    <table>
        <tr>
            <td>
                @if ($logo)
                    <img class="logo" src="{{ $logo }}" alt="Donat Wawa">
                @else
                    <div class="h2" style="margin:0">Donat Wawa</div>
                @endif
            </td>
            <td class="right" style="vertical-align: middle;">
                <div class="eyebrow">{{ strtoupper(__('Sales Report')) }}</div>
                <div class="printed">{{ __('Printed on') }}: {{ $printedAt }}</div>
            </td>
        </tr>
    </table>

@php
    $cmpText = is_null($comparison['change'])
        ? __('No data for :month', ['month' => $comparison['label']])
        : ($comparison['change'] > 0
            ? __('Up :percent% from :month', ['percent' => number_format($comparison['change'], 1, ',', '.'), 'month' => $comparison['label']])
            : ($comparison['change'] < 0
                ? __('Down :percent% from :month', ['percent' => number_format(abs($comparison['change']), 1, ',', '.'), 'month' => $comparison['label']])
                : __('Same as :month', ['month' => $comparison['label']])));
@endphp
    <div class="hero">
        <table><tr>
            <td style="vertical-align: middle;">
                <span class="chip">{{ strtoupper(__('Period')) }}</span>
                <span class="mon">{{ $period }}</span>
            </td>
            <td class="right" style="vertical-align: middle;"><span class="cmp">{{ $cmpText }}</span></td>
        </tr></table>
        <div class="hline"></div>
        <table class="strip"><tr>
            <td class="srev"><div class="rev-label">{{ strtoupper(__('Revenue')) }}</div><div class="rev" style="font-size: 32px;">{{ $money($summary['revenue']) }}</div></td>
            <td class="sc"><div class="stat-val">{{ $summary['orders'] }}</div><div class="stat-label">{{ __('Total Orders') }}</div></td>
            <td class="sc"><div class="stat-val">{{ $summary['done'] }}</div><div class="stat-label">{{ __('Completed Orders') }}</div></td>
            <td class="sc"><div class="stat-val">{{ $summary['running'] }}</div><div class="stat-label">{{ __('Running Orders') }}</div></td>
            <td class="sc"><div class="stat-val">{{ $summary['rejected'] }}</div><div class="stat-label">{{ __('Rejected Orders') }}</div></td>
        </tr></table>
    </div>

        <div class="h2" style="margin-top: 18px;">{{ __('Best Selling Products') }}</div>
        <table class="data">
            <thead>
                <tr>
                    <th class="center" style="width: 7%">{{ __('No.') }}</th>
                    <th style="width: 25%">{{ __('Product') }}</th>
                    <th style="width: 22%"></th>
                    <th class="right" style="width: 18%">{{ __('Qty Sold') }}</th>
                    <th class="right" style="width: 28%">{{ __('Sales') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr>
                        <td class="center muted">{{ $loop->iteration }}</td>
                        <td>{{ $product['name'] }}</td>
                        <td><div class="bar-bg"><div class="bar" style="width: {{ max(4, round($product['quantity'] / $topQty * 100)) }}%;"></div></div></td>
                        <td class="right">{{ $product['quantity'] }}</td>
                        <td class="right strong">{{ $money($product['total']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty">{{ __('No sales in this period.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="h2" style="margin-top: 22px;">{{ __('Order Details') }}</div>
        <table class="data">
            <thead>
                <tr>
                    <th class="center" style="width: 4%">{{ __('No.') }}</th>
                    <th style="width: 14%">{{ __('Code') }}</th>
                    <th style="width: 11%">{{ __('Date') }}</th>
                    <th style="width: 12%">{{ __('Customer') }}</th>
                    <th>{{ __('Items') }}</th>
                    <th style="width: 8%">{{ __('Delivery') }}</th>
                    <th style="width: 9%">{{ __('Payment') }}</th>
                    <th class="right" style="width: 11%">{{ __('Total') }}</th>
                    <th style="width: 10%">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td class="center muted">{{ $loop->iteration }}</td>
                        <td class="code">{{ $order->display_code }}</td>
                        <td>{{ $order->placed_at->translatedFormat('d M Y H:i') }}</td>
                        <td>{{ $order->customer_name }}</td>
                        <td>{{ $order->items->map(fn ($item) => $item->name . ' (' . $item->quantity . ')')->implode(', ') }}</td>
                        <td>{{ $order->delivery_label }}</td>
                        <td>{{ $order->transaction?->metode_pembayaran ?? '-' }}</td>
                        <td class="right strong">{{ $money($order->total) }}</td>
                        <td><span class="dot" style="color: {{ $statusColors[$order->status] ?? '#4e342e' }};">&#9679;</span> <span class="strong" style="color: {{ $statusColors[$order->status] ?? '#4e342e' }};">{{ $statusLabels[$order->status] ?? $order->status }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="empty">{{ __('No orders in this period.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
</body>

</html>
