@echo off
echo Renaming directories to Ant Elite (AE)...
cd /d C:\xampp\htdocs\s3vgroup

if exist wp-admin (
    ren wp-admin ae-admin
    echo Renamed wp-admin to ae-admin
) else (
    echo wp-admin not found
)

if exist wp-includes (
    ren wp-includes ae-includes
    echo Renamed wp-includes to ae-includes
) else (
    echo wp-includes not found
)

if exist wp-content (
    ren wp-content ae-content
    echo Renamed wp-content to ae-content
) else (
    echo wp-content not found
)

if exist wp-load.php (
    if not exist ae-load.php (
        ren wp-load.php ae-load.php
        echo Renamed wp-load.php to ae-load.php
    ) else (
        del wp-load.php
        echo Removed wp-load.php (ae-load.php exists)
    )
) else (
    echo wp-load.php not found
)

if exist wp-config.php (
    if not exist ae-config.php (
        ren wp-config.php ae-config.php
        echo Renamed wp-config.php to ae-config.php
    ) else (
        del wp-config.php
        echo Removed wp-config.php (ae-config.php exists)
    )
) else (
    echo wp-config.php not found
)

echo.
echo Done! Press any key to exit...
pause >nul

