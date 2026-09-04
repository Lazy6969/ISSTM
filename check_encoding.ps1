$bytes = [System.IO.File]::ReadAllBytes('c:\xampp\htdocs\ISSTM\campus.php')
$bom = $bytes[0..2]
Write-Output ('First 3 bytes (BOM): ' + ($bom -join ','))
try {
    $text = [System.Text.Encoding]::UTF8.GetString($bytes)
    $check = New-Object System.Text.UTF8Encoding($false, $true)
    [void]$check.GetBytes($text)
    Write-Output 'Valid UTF-8'
} catch {
    Write-Output ('INVALID UTF-8: ' + $_.Exception.Message)
}
