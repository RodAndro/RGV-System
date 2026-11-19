<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f3f4f6; }
        .container { max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #2563eb, #1e40af); color: white; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 24px; }
        .body p { color: #374151; line-height: 1.6; margin: 8px 0; }
        .info-box { background: #f9fafb; border-left: 4px solid #2563eb; padding: 16px; margin: 16px 0; border-radius: 4px; }
        .info-box strong { color: #374151; }
        .btn { display: inline-block; background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 600; margin-top: 16px; }
        .footer { background: #f9fafb; padding: 16px 24px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>RGV Multi-Tech Services</h1>
        </div>
        <div class="body">
            <p><strong>{{ $action }}</strong></p>
            <div class="info-box">
                <p><strong>Type:</strong> {{ $entityType }}</p>
                <p><strong>Entity:</strong> {{ $entityName }}</p>
                <p><strong>Details:</strong> {{ $details }}</p>
            </div>
            <p>This is an automated notification from the RGV Multi-Tech Services system.</p>
            @if($link)
                <a href="{{ $link }}" class="btn">View Details</a>
            @endif
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} RGV Multi-Tech Services. All rights reserved.</p>
            <p>This is an automated email — please do not reply.</p>
        </div>
    </div>
</body>
</html>
