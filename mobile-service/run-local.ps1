# Starts the standalone mobile API on this computer using mobile-service/.env.
# Employees on the same network can then sign in with the app using the printed URL.
#
# If a phone cannot reach this computer, allow inbound TCP 8081 once by running
# PowerShell as Administrator:
#   .\run-local.ps1 -OpenFirewall
param([switch]$OpenFirewall)

$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot

if ($OpenFirewall) {
    $principal = New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())
    if (-not $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
        throw 'Run this script as Administrator when using -OpenFirewall.'
    }
    if (-not (Get-NetFirewallRule -DisplayName 'RGV mobile API (TCP 8081)' -ErrorAction SilentlyContinue)) {
        New-NetFirewallRule -DisplayName 'RGV mobile API (TCP 8081)' `
            -Direction Inbound -Protocol TCP -LocalPort 8081 -Action Allow `
            -Profile Private,Public | Out-Null
        Write-Host 'Added firewall rule: RGV mobile API (TCP 8081)'
    }
}

if (-not (Test-Path '.env')) {
    throw 'mobile-service/.env is missing. Copy .env.example and fill in the database and audit settings.'
}

Get-Content '.env' | ForEach-Object {
    if ($_ -match '^\s*#') { return }
    if ($_ -match '^([A-Za-z_][A-Za-z0-9_]*)=(.*)$') {
        [Environment]::SetEnvironmentVariable($matches[1], $matches[2].Trim().Trim('"'), 'Process')
    }
}

$addresses = Get-NetIPAddress -AddressFamily IPv4 |
    Where-Object { $_.IPAddress -notmatch '^127\.' -and $_.PrefixOrigin -ne 'WellKnown' } |
    Select-Object -ExpandProperty IPAddress

Write-Host 'RGV mobile API (use one of these in the app under "Server"):'
foreach ($address in $addresses) {
    Write-Host "  http://${address}:8081/api/v1/mobile"
}
Write-Host ''

& "$PSScriptRoot\.venv\Scripts\python.exe" -m uvicorn app.main:app --host 0.0.0.0 --port 8081
