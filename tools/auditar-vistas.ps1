# Static heuristic audit. Run from repository root.
$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
$files = @()
foreach ($folder in @('public', 'views')) {
    $files += @(Get-ChildItem -LiteralPath (Join-Path $root $folder) -Recurse -File -Filter '*.php')
}
$findings = New-Object 'System.Collections.Generic.List[string]'
foreach ($file in $files) {
    $relative = $file.FullName.Substring($root.Length + 1)
    $lines = [IO.File]::ReadAllLines($file.FullName)
    for ($i = 0; $i -lt $lines.Length; $i++) {
        $line = $lines[$i]
        if ($line -match '(?i)\bstyle\s*=\s*["'']' -or $line -match '(?i)<style(?:\s|>)') {
            $findings.Add($relative + ':' + ($i + 1) + ': inline style candidate')
        }
        if ($line -match '<\?=\s*(.*?)\s*\?>') {
            $expr = $Matches[1].Trim()
            if ($expr -notmatch '^e\s*\(' -and $expr -notmatch '^\$\w+\s*\?' -and $expr -notmatch '^\d+') {
                $findings.Add($relative + ':' + ($i + 1) + ': review PHP output: ' + $expr)
            }
        }
    }
}
Write-Host ('PHP files inspected: ' + $files.Count)
if ($findings.Count -gt 0) {
    Write-Host 'Candidates for manual review (not proof of a vulnerability):'
    foreach ($finding in $findings) { Write-Host ('  ' + $finding) }
    exit 1
}
Write-Host 'No candidates found by heuristic checks.'
Write-Host 'Manual inspection and runtime validation are still required.'