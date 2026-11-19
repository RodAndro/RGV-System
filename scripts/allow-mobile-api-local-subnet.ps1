#Requires -RunAsAdministrator
$ErrorActionPreference = 'Stop'

$legacyRuleNames = @(
    'RGV-Laravel-Mobile-API-Current-WiFi',
    'RGV-Laravel-Mobile-API-8000-Private-LAN'
)

foreach ($ruleName in $legacyRuleNames) {
    Get-NetFirewallRule -Name $ruleName -ErrorAction SilentlyContinue |
        Remove-NetFirewallRule
}

$serverProcess = Get-CimInstance Win32_Process |
    Where-Object {
        $_.Name -eq 'php.exe' -and
        $_.CommandLine -match 'artisan serve --host=0\.0\.0\.0 --port=8000'
    } |
    Select-Object -First 1

$phpPath = $serverProcess.ExecutablePath
if (-not $phpPath) {
    $phpPath = (Get-Command php.exe -ErrorAction Stop).Source
}

foreach ($ruleName in @(
    'RGV-Laravel-Mobile-API-TCP-LocalSubnet',
    'RGV-Mobile-API-Discovery-UDP-LocalSubnet'
)) {
    Get-NetFirewallRule -Name $ruleName -ErrorAction SilentlyContinue |
        Remove-NetFirewallRule
}

New-NetFirewallRule `
    -Name 'RGV-Laravel-Mobile-API-TCP-LocalSubnet' `
    -DisplayName 'RGV Laravel Mobile API (TCP 8000, local subnet)' `
    -Direction Inbound `
    -Action Allow `
    -Protocol TCP `
    -Program $phpPath `
    -LocalPort 8000 `
    -LocalAddress Any `
    -RemoteAddress LocalSubnet `
    -Profile Any | Out-Null

New-NetFirewallRule `
    -Name 'RGV-Mobile-API-Discovery-UDP-LocalSubnet' `
    -DisplayName 'RGV Mobile API discovery (UDP 39555, local subnet)' `
    -Direction Inbound `
    -Action Allow `
    -Protocol UDP `
    -LocalPort 39555 `
    -LocalAddress Any `
    -RemoteAddress LocalSubnet `
    -Profile Any | Out-Null

Write-Host "Allowed Laravel API TCP 8000 and local discovery UDP 39555 from LocalSubnet. PHP: $phpPath"
