param(
    [string]$ProjectRoot = (Split-Path -Parent $PSScriptRoot)
)

$ErrorActionPreference = 'Stop'
$outputDir = Join-Path $ProjectRoot 'output'
$stageDir = Join-Path $outputDir 'primaria-porumbesti-elementor'
$zipPath = Join-Path $outputDir 'primaria-porumbesti-elementor.zip'

New-Item -ItemType Directory -Path $outputDir -Force | Out-Null
if (Test-Path -LiteralPath $stageDir) {
    $resolvedStage = (Resolve-Path -LiteralPath $stageDir).Path
    if ($resolvedStage -ne $stageDir -or -not $resolvedStage.StartsWith($outputDir, [StringComparison]::OrdinalIgnoreCase)) {
        throw 'Unexpected staging path.'
    }
    Remove-Item -LiteralPath $stageDir -Recurse -Force
}
if (Test-Path -LiteralPath $zipPath) {
    Remove-Item -LiteralPath $zipPath -Force
}

New-Item -ItemType Directory -Path (Join-Path $stageDir 'assets/css') -Force | Out-Null
New-Item -ItemType Directory -Path (Join-Path $stageDir 'assets/js') -Force | Out-Null
New-Item -ItemType Directory -Path (Join-Path $stageDir 'assets/fonts') -Force | Out-Null
New-Item -ItemType Directory -Path (Join-Path $stageDir 'assets/images') -Force | Out-Null

Copy-Item -LiteralPath (Join-Path $ProjectRoot 'includes') -Destination $stageDir -Recurse
Copy-Item -LiteralPath (Join-Path $ProjectRoot 'templates') -Destination $stageDir -Recurse
Copy-Item -LiteralPath (Join-Path $ProjectRoot 'assets/css/editor.css') -Destination (Join-Path $stageDir 'assets/css')
Copy-Item -LiteralPath (Join-Path $ProjectRoot 'assets/css/fonts.css') -Destination (Join-Path $stageDir 'assets/css')
Copy-Item -LiteralPath (Join-Path $ProjectRoot 'assets/css/frontend.css') -Destination (Join-Path $stageDir 'assets/css')
Copy-Item -LiteralPath (Join-Path $ProjectRoot 'assets/js/frontend.js') -Destination (Join-Path $stageDir 'assets/js')
Copy-Item -Path (Join-Path $ProjectRoot 'assets/fonts/*.woff2') -Destination (Join-Path $stageDir 'assets/fonts')
Copy-Item -LiteralPath (Join-Path $ProjectRoot 'assets/images/porumbesti-monogram.svg') -Destination (Join-Path $stageDir 'assets/images')
Copy-Item -LiteralPath (Join-Path $ProjectRoot 'assets/images/favicon.svg') -Destination (Join-Path $stageDir 'assets/images')

foreach ($file in @('primaria-porumbesti-elementor.php', 'uninstall.php', 'readme.txt', 'README.md', 'README-HU.md')) {
    Copy-Item -LiteralPath (Join-Path $ProjectRoot $file) -Destination $stageDir
}

Compress-Archive -LiteralPath $stageDir -DestinationPath $zipPath -CompressionLevel Optimal

Add-Type -AssemblyName System.IO.Compression.FileSystem
$archive = [System.IO.Compression.ZipFile]::OpenRead($zipPath)
$entries = @($archive.Entries | ForEach-Object { $_.FullName.Replace('\', '/') })
$archive.Dispose()

if (-not ($entries -contains 'primaria-porumbesti-elementor/primaria-porumbesti-elementor.php')) {
    throw 'Plugin bootstrap is missing from ZIP.'
}
if (-not ($entries -contains 'primaria-porumbesti-elementor/assets/images/porumbesti-monogram.svg')) {
    throw 'Institutional monogram is missing from ZIP.'
}
if ($entries | Where-Object { $_ -like '*prototype*' -or $_ -like '*/tests/*' -or $_ -like '*/tools/*' }) {
    throw 'Development-only file leaked into plugin ZIP.'
}

$resolvedStage = (Resolve-Path -LiteralPath $stageDir).Path
if ($resolvedStage -ne $stageDir -or -not $resolvedStage.StartsWith($outputDir, [StringComparison]::OrdinalIgnoreCase)) {
    throw 'Refusing to remove an unexpected staging directory.'
}
Remove-Item -LiteralPath $stageDir -Recurse -Force

$hash = Get-FileHash -LiteralPath $zipPath -Algorithm SHA256
[PSCustomObject]@{
    Path = $zipPath
    Bytes = (Get-Item -LiteralPath $zipPath).Length
    Entries = $entries.Count
    SHA256 = $hash.Hash
}
