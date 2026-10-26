# Script untuk memastikan PHP Server berjalan sebelum Chrome dibuka di VS Code
$port = 8000
$workspaceDir = "d:\semseter 5\menejemen web gis"
$phpBin = if (Test-Path "C:\xampp\php\php.exe") { "C:\xampp\php\php.exe" } else { (Get-Command php -ErrorAction SilentlyContinue).Source }

if (-not $phpBin) {
    Write-Error "[ERROR] PHP tidak ditemukan di C:\xampp\php\php.exe maupun sistem PATH."
    exit 1
}

function Test-PortOpen([string]$ip, [int]$p) {
    try {
        $client = New-Object System.Net.Sockets.TcpClient
        $connect = $client.BeginConnect($ip, $p, $null, $null)
        $wait = $connect.AsyncWaitHandle.WaitOne(300, $false)
        if ($wait -and $client.Connected) {
            $client.EndConnect($connect)
            $client.Close()
            return $true
        }
        $client.Close()
    } catch {
        return $false
    }
    return $false
}

# Cek apakah port 8000 sudah berjalan
if (Test-PortOpen "127.0.0.1" $port) {
    Write-Host "[OK] Server BahariGIS sudah aktif di http://localhost:$port"
    exit 0
}

Write-Host "[INFO] Memulai PHP Server di port $port..."
Start-Process -FilePath $phpBin -ArgumentList "-S localhost:$port" -WorkingDirectory $workspaceDir -WindowStyle Hidden

# Tunggu sampai port merespons (maksimal 4 detik)
$started = $false
for ($i = 0; $i -lt 20; $i++) {
    Start-Sleep -Milliseconds 200
    if (Test-PortOpen "127.0.0.1" $port) {
        $started = $true
        break
    }
}

if ($started) {
    Write-Host "[OK] Server BahariGIS aktif di http://localhost:$port"
    exit 0
} else {
    Write-Warning "[WARN] Server sedang memulai..."
    exit 0
}
