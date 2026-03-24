# Fitorebet Deployment Script for Windows
# This script will push your changes to GitHub and run setup commands on the server.

Write-Host "--- Starting Deployment to Fitorebet ---" -ForegroundColor Cyan

# 1. Push to GitHub
Write-Host "1. Pushing changes to GitHub..." -ForegroundColor Yellow
git push origin main
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error: Git push failed. Please check your GitHub credentials." -ForegroundColor Red
    exit $LASTEXITCODE
}

# 2. Run commands on server via SSH
Write-Host "2. Running setup commands on the server..." -ForegroundColor Yellow
$server = "fitorebet1@155.254.35.230"
$commands = "cd /home/fitorebet1/ && git pull origin main && php artisan config:cache && php artisan view:cache && php artisan filament:optimize"

# Note: This will prompt for your SSH password once.
ssh -o StrictHostKeyChecking=no $server $commands

if ($LASTEXITCODE -eq 0) {
    Write-Host "--- Deployment Successful! ---" -ForegroundColor Green
} else {
    Write-Host "--- Deployment failed at the server step. Please check the error above. ---" -ForegroundColor Red
}
