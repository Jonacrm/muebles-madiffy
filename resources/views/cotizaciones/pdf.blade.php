<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Cotización {{ $cotizacion['folio'] }}</title>
    <style>
        @page {
            margin: 28px;
        }

        body {
            color: #111827;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 11px;
            line-height: 1.35;
        }

        h1, h2, h3, p {
            margin: 0;
        }

        .header {
            border-bottom: 3px solid #4338ca;
            margin-bottom: 20px;
            padding-bottom: 14px;
        }

        .brand {
            color: #312e81;
            font-size: 22px;
            font-weight: 700;
        }

        .brand-logo {
            height: 42px;
            width: auto;
        }

        .brand-text {
            padding-left: 10px;
            vertical-align: middle;
        }

        .subtitle {
            color: #4b5563;
            margin-top: 4px;
        }

        .generated-info {
            color: #4b5563;
            font-size: 10px;
            margin-top: 8px;
        }

        .folio {
            color: #312e81;
            font-size: 17px;
            font-weight: 700;
            text-align: right;
        }

        .status {
            color: #4b5563;
            margin-top: 4px;
            text-align: right;
        }

        .row {
            width: 100%;
        }

        .row td {
            vertical-align: top;
        }

        .section {
            margin-top: 16px;
        }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px;
        }

        .label {
            color: #6b7280;
            font-size: 10px;
            text-transform: uppercase;
        }

        .value {
            font-weight: 700;
            margin-top: 3px;
        }

        table.items {
            border-collapse: collapse;
            margin-top: 10px;
            width: 100%;
        }

        table.items th {
            background: #eef2ff;
            color: #3730a3;
            font-size: 10px;
            text-align: left;
        }

        table.items th,
        table.items td {
            border: 1px solid #e5e7eb;
            padding: 7px;
        }

        .text-right {
            text-align: right;
        }

        .totals {
            margin-left: auto;
            margin-top: 18px;
            width: 260px;
        }

        .totals td {
            border-bottom: 1px solid #e5e7eb;
            padding: 6px 0;
        }

        .total-final td {
            border-bottom: 0;
            color: #312e81;
            font-size: 14px;
            font-weight: 700;
            padding-top: 9px;
        }

    </style>
</head>
<body>
    <table class="header row">
        <tr>
            <td style="width: 55%;">
                <table>
                    <tr>
                        <td>
                            <img src="{{ public_path('images/logo-mueblify.png') }}" alt="Mueblify" class="brand-logo">
                        </td>
                        <td class="brand-text">
                            <h1 class="brand">Mueblify</h1>
                            <p class="subtitle">Cotización de mobiliario</p>
                            <p class="generated-info">Hermosillo, Sonora, México.</p>
                            <p class="generated-info">Generado el {{ now()->format('d/m/Y') }}</p>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 45%;">
                <p class="folio">{{ $cotizacion['folio'] }}</p>
                <p class="status">Estado: {{ $cotizacion['estado'] }}</p>
            </td>
        </tr>
    </table>

    <table class="row section">
        <tr>
            <td style="width: 50%; padding-right: 8px;">
                <div class="card">
                    <p class="label">Cliente</p>
                    <p class="value">{{ $cotizacion['cliente'] }}</p>
                    @if ($cotizacion['rfc'])
                        <p>RFC: {{ $cotizacion['rfc'] }}</p>
                    @endif
                    @if ($cotizacion['cliente_email'])
                        <p>Email: {{ $cotizacion['cliente_email'] }}</p>
                    @endif
                    @if ($cotizacion['cliente_phone'])
                        <p>Teléfono: {{ $cotizacion['cliente_phone'] }}</p>
                    @endif
                    @if ($cotizacion['cliente_address'])
                        <p>Dirección: {{ $cotizacion['cliente_address'] }}</p>
                    @endif
                </div>
            </td>
            <td style="width: 50%; padding-left: 8px;">
                <div class="card">
                    <p class="label">Datos de cotización</p>
                    <p>Vendedor: <strong>{{ $cotizacion['vendedor'] }}</strong></p>
                    <p>Emisión: <strong>{{ $cotizacion['fecha_emision'] }}</strong></p>
                    <p>Vigencia: <strong>{{ $cotizacion['vigencia'] }}</strong></p>
                </div>
            </td>
        </tr>
    </table>

    <div class="section">
        <h2 style="color: #312e81; font-size: 15px;">Conceptos</h2>

        <table class="items">
            <thead>
                <tr>
                    <th style="width: 13%;">SKU</th>
                    <th style="width: 24%;">Producto</th>
                    <th style="width: 27%;">Descripción</th>
                    <th class="text-right" style="width: 8%;">Cant.</th>
                    <th class="text-right" style="width: 10%;">Precio</th>
                    <th class="text-right" style="width: 8%;">Desc.</th>
                    <th class="text-right" style="width: 10%;">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cotizacion['lineas'] as $linea)
                    <tr>
                        <td>{{ $linea['sku'] }}</td>
                        <td>{{ $linea['producto'] }}</td>
                        <td>{{ $linea['descripcion'] }}</td>
                        <td class="text-right">{{ $linea['cantidad'] }}</td>
                        <td class="text-right">${{ number_format($linea['precio_unitario'], 2) }}</td>
                        <td class="text-right">${{ number_format($linea['descuento_linea'], 2) }}</td>
                        <td class="text-right">${{ number_format($linea['subtotal'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td class="text-right">${{ number_format($cotizacion['subtotal'], 2) }}</td>
        </tr>
        <tr>
            <td>Descuento global</td>
            <td class="text-right">${{ number_format($cotizacion['descuento_global'], 2) }}</td>
        </tr>
        <tr>
            <td>Base gravable</td>
            <td class="text-right">${{ number_format($cotizacion['base'], 2) }}</td>
        </tr>
        <tr>
            <td>IVA 16%</td>
            <td class="text-right">${{ number_format($cotizacion['iva'], 2) }}</td>
        </tr>
        <tr class="total-final">
            <td>Total</td>
            <td class="text-right">${{ number_format($cotizacion['total'], 2) }}</td>
        </tr>
    </table>

</body>
</html>
