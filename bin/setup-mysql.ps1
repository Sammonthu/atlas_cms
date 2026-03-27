# Définit un script PowerShell d'initialisation MySQL pour Atlas CMS.
param()

# Définit le chemin du dossier du script.
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
# Définit le chemin racine du projet.
$projectDir = Resolve-Path (Join-Path $scriptDir '..')
# Définit le chemin du php.ini local dédié Atlas.
$phpIniPath = Join-Path $projectDir 'config\php\php-cli-atlas.ini'
# Définit le chemin du fichier .env.local.
$envLocalPath = Join-Path $projectDir '.env.local'

# Demande l'hôte MySQL à l'utilisateur.
$dbHost = Read-Host 'Hote MySQL (defaut: 127.0.0.1)'
# Applique la valeur par défaut de l'hôte.
if ([string]::IsNullOrWhiteSpace($dbHost)) { $dbHost = '127.0.0.1' }

# Demande le port MySQL à l'utilisateur.
$port = Read-Host 'Port MySQL (defaut: 3306)'
# Applique la valeur par défaut du port.
if ([string]::IsNullOrWhiteSpace($port)) { $port = '3306' }

# Demande l'utilisateur MySQL à l'utilisateur.
$user = Read-Host 'Utilisateur MySQL (defaut: root)'
# Applique la valeur par défaut de l'utilisateur.
if ([string]::IsNullOrWhiteSpace($user)) { $user = 'root' }

# Demande le mot de passe MySQL à l'utilisateur.
$password = Read-Host 'Mot de passe MySQL'

# Demande le nom de la base cible à l'utilisateur.
$dbName = Read-Host 'Nom de la base (defaut: cms_symfony)'
# Applique la valeur par défaut de base.
if ([string]::IsNullOrWhiteSpace($dbName)) { $dbName = 'cms_symfony' }

# Définit la version serveur MySQL attendue.
$serverVersion = Read-Host 'Version MySQL (defaut: 8.0)'
# Applique la version par défaut.
if ([string]::IsNullOrWhiteSpace($serverVersion)) { $serverVersion = '8.0' }

# Encode le mot de passe pour URL DSN.
$encodedPassword = [System.Uri]::EscapeDataString($password)
# Construit la variable DATABASE_URL complète.
$databaseUrl = "mysql://$user`:$encodedPassword@$dbHost`:$port/$dbName?serverVersion=$serverVersion&charset=utf8mb4"

# Construit le contenu final du fichier .env.local.
$envLocalContent = @(
    '# Définit l''environnement Symfony local.'
    'APP_ENV=dev'
    '# Définit le secret local Symfony.'
    'APP_SECRET=change_me_local_secret'
    '# Définit la connexion MySQL Doctrine locale.'
    ('DATABASE_URL="' + $databaseUrl + '"')
) -join [Environment]::NewLine

# Écrit le fichier .env.local en UTF-8 sans BOM.
$utf8NoBom = New-Object System.Text.UTF8Encoding($false)
# Persiste le contenu .env.local dans le projet.
[System.IO.File]::WriteAllText($envLocalPath, $envLocalContent, $utf8NoBom)

# Affiche la commande de test d'extensions PHP.
Write-Host 'Test extensions PHP:' -ForegroundColor Cyan
# Exécute la vérification pdo_mysql/mysqli avec php.ini local.
php -c $phpIniPath -m | findstr /I "pdo_mysql mysqli"

# Affiche la commande de création de base.
Write-Host 'Creation base si necessaire...' -ForegroundColor Cyan
# Crée la base si elle n'existe pas déjà.
php -c $phpIniPath (Join-Path $projectDir 'bin\console') doctrine:database:create --if-not-exists

# Affiche la commande de migration.
Write-Host 'Execution migrations...' -ForegroundColor Cyan
# Exécute les migrations Doctrine.
php -c $phpIniPath (Join-Path $projectDir 'bin\console') doctrine:migrations:migrate -n

# Affiche la commande de validation de schéma.
Write-Host 'Validation schema...' -ForegroundColor Cyan
# Valide la cohérence mapping/base.
php -c $phpIniPath (Join-Path $projectDir 'bin\console') doctrine:schema:validate

# Affiche un message final de réussite.
Write-Host 'Configuration MySQL terminee pour Atlas CMS.' -ForegroundColor Green
