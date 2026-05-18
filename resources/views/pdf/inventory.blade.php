<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report - RGV Multi-Tech Services</title>
    @include('pdf.partials.styles')
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 15px; table-layout: fixed; word-wrap: break-word; }
        th { background-color: #468a3f; color: white; padding: 8px 6px; text-align: left; font-size: 10px; }
        td { border: 1px solid #e5e7eb; padding: 6px; font-size: 10px; overflow-wrap: break-word; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .col-code { width: 10%; }
        .col-name { width: 14%; }
        .col-category { width: 10%; }
        .col-supplier { width: 10%; }
        .col-qty { width: 7%; }
        .col-unit { width: 7%; }
        .col-cost { width: 10%; }
        .col-status { width: 9%; }
        .col-condition { width: 9%; }
        .col-location { width: 14%; }
    </style>
</head>
<body>
    @include('pdf.partials.header', ['title' => 'Inventory Report'])

    <table>
        <thead>
            <tr>
                <th class="col-code">Item Code</th>
                <th class="col-name">Name</th>
                <th class="col-category">Category</th>
                <th class="col-supplier">Supplier</th>
                <th class="col-qty">Qty</th>
                <th class="col-unit">Unit</th>
                <th class="col-cost">Unit Cost</th>
                <th class="col-status">Status</th>
                <th class="col-condition">Condition</th>
                <th class="col-location">Location</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inventories as $inventory)
                <tr>
                    <td>{{ $inventory->item_code }}</td>
                    <td>{{ $inventory->name }}</td>
                    <td>{{ $inventory->category ? $inventory->category->name : 'N/A' }}</td>
                    <td>{{ $inventory->supplier ? $inventory->supplier->name : 'N/A' }}</td>
                    <td>
                        {{ $inventory->quantity }}
                        @if($inventory->isLowStock())
                            <span class="low-stock"> (Low)</span>
                        @endif
                    </td>
                    <td>{{ $inventory->unit }}</td>
                    <td>{{ $inventory->unit_cost ? '₱' . number_format($inventory->unit_cost, 2) : 'N/A' }}</td>
                    <td><span class="badge badge-{{ $inventory->status }}">{{ ucfirst($inventory->status) }}</span></td>
                    <td>{{ ucfirst($inventory->condition) }}</td>
                    <td>{{ $inventory->location ?: 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-row">
        <p>Total Items: {{ $inventories->count() }}</p>
    </div>
</body>
</html>
