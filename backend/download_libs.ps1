# Create vendor directory if it doesn't exist
$vendorDir = "$PSScriptRoot\vendor"
if (-not (Test-Path $vendorDir)) {
    New-Item -ItemType Directory -Path $vendorDir | Out-Null
}

# Function to download and extract ZIP
function Download-Extract-Zip($url, $targetDir) {
    $tempFile = [System.IO.Path]::GetTempFileName()
    $tempFile = [System.IO.Path]::ChangeExtension($tempFile, 'zip')
    
    Write-Host "Downloading $url..."
    Invoke-WebRequest -Uri $url -OutFile $tempFile
    
    Write-Host "Extracting to $targetDir..."
    Expand-Archive -Path $tempFile -DestinationPath $targetDir -Force
    Remove-Item $tempFile
}

# Download and extract firebase/php-jwt
$jwtUrl = "https://github.com/firebase/php-jwt/archive/refs/tags/v6.0.0.zip"
$jwtDir = "$vendorDir\firebase\php-jwt"
if (-not (Test-Path $jwtDir)) {
    Download-Extract-Zip -url $jwtUrl -targetDir $vendorDir
    Rename-Item -Path "$vendorDir\php-jwt-6.0.0" -NewName "php-jwt"
}

# Create autoload.php
$autoloadContent = @'
<?php

spl_autoload_register(function ($class) {
    $prefix = 'Firebase\\JWT\\';
    $base_dir = __DIR__ . '/firebase/php-jwt/src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});
'@

Set-Content -Path "$vendorDir\autoload.php" -Value $autoloadContent

Write-Host "Libraries installed successfully!" -ForegroundColor Green
